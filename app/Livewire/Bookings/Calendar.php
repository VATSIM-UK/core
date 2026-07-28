<?php

declare(strict_types=1);

namespace App\Livewire\Bookings;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Repositories\Cts\BookingRepository;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('livewire.bookings.layout', [
    '_pageTitle' => 'Bookings Calendar',
])]
class Calendar extends Component
{
    public Carbon $selectedDate;

    public Collection $bookings;

    public Collection $qualifiedPositions;

    public string $positionFilter = '';

    public array $timelinePositions = [];

    public array $timelineScale = [];

    public int $filterVersion = 0;

    public int $dataVersion = 0;

    public function mount(?int $year = null, ?int $month = null): void
    {
        $this->selectedDate = Carbon::today();

        if ($year) {
            $day = request()->input('day', 1);
            $this->selectedDate = Carbon::create($year, $month ?? $this->selectedDate->month, (int) $day);
        }

        $this->bookings = collect();
        $this->qualifiedPositions = collect();
        $this->timelinePositions = [];
        $this->refreshData();
    }

    public function render()
    {
        return view('livewire.bookings.calendar', [
            'bookings' => $this->bookings,
            'qualifiedPositions' => $this->qualifiedPositions,
            'timelinePositions' => $this->timelinePositions,
            'timelineHours' => $this->getTimelineHours(),
            'selectedDate' => $this->selectedDate,
            'timelineScale' => array_values($this->timelineScale),
        ]);
    }

    private function refreshData(): void
    {
        $this->getBookingsForDate($this->selectedDate);
        $this->computeScale();
        $this->getQualifiedPositions();
        $this->buildTimeline();
        $this->dataVersion++;
    }

    public function updatedPositionFilter(): void
    {
        $this->filterVersion++;
        $this->buildTimeline();
    }

    public function getBookingsForDate(Carbon $date): void
    {
        $this->bookings = app(BookingRepository::class)->getBookings($date);
    }

    public function getQualifiedPositions(): void
    {
        $rating = (int) (auth()->user()?->qualification_atc?->vatsim ?? 0);
        $maxAllowed = $rating + 1;

        $bookableTypes = [
            Position::TYPE_DELIVERY,
            Position::TYPE_GROUND,
            Position::TYPE_TOWER,
            Position::TYPE_APPROACH,
            Position::TYPE_ENROUTE,
            Position::TYPE_FSS,
        ];

        $allowedTypes = array_filter($bookableTypes, function (int $type) use ($maxAllowed): bool {
            return Position::minimumVatsimRatingForType($type) <= $maxAllowed;
        });

        $query = Position::real()->orderBy('callsign');

        if (! empty($allowedTypes)) {
            $query->whereIn('type', $allowedTypes);
        }

        $this->qualifiedPositions = $query->pluck('callsign', 'id');

        if ($this->qualifiedPositions->isEmpty()) {
            $this->qualifiedPositions = Position::real()
                ->orderBy('callsign')
                ->pluck('callsign', 'id');
        }
    }

    public function buildTimeline(): void
    {
        $groups = [];
        $singles = [];

        foreach ($this->bookings as $booking) {
            $callsign = $booking->position ?? 'Unknown';

            if ($this->positionFilter !== '' && ! str_starts_with(strtoupper($callsign), strtoupper($this->positionFilter))) {
                continue;
            }

            $start = $this->timeToMinutes($booking->from);
            $end = $this->timeToMinutes($booking->to);

            $bookingData = [
                'id' => $booking->id,
                'source' => $booking->source,
                'cts_booking_id' => $booking->cts_booking_id,
                'from' => $booking->from,
                'to' => $booking->to,
                'startMin' => $start,
                'endMin' => $end,
                'left_pct' => $this->scalePos($start),
                'width_pct' => $this->scaleWidth($start, $end),
                'member' => $booking->member,
                'type' => $booking->type,
            ];

            $parts = explode('_', $callsign);
            $prefix = $parts[0] ?? '';
            $isIcao = strlen($prefix) === 4 && ctype_alpha($prefix);

            if ($isIcao) {
                if (! isset($groups[$prefix])) {
                    $groups[$prefix] = [];
                }
                if (! isset($groups[$prefix][$callsign])) {
                    $groups[$prefix][$callsign] = [
                        'callsign' => $callsign,
                        'position_id' => $booking->position_id,
                        'bookings' => [],
                    ];
                }
                $groups[$prefix][$callsign]['bookings'][] = $bookingData;
            } else {
                if (! isset($singles[$callsign])) {
                    $singles[$callsign] = [
                        'callsign' => $callsign,
                        'position_id' => $booking->position_id,
                        'bookings' => [],
                    ];
                }
                $singles[$callsign]['bookings'][] = $bookingData;
            }
        }

        $result = [];

        ksort($groups);
        foreach ($groups as $icao => $positions) {
            ksort($positions);
            $posArray = array_values($positions);
            $clusters = $this->buildTimeClusters($posArray);
            $result[] = [
                'type' => 'group',
                'icao' => $icao,
                'positions' => $posArray,
                'clusters' => $clusters,
            ];
        }

        ksort($singles);
        if (! empty($groups) && ! empty($singles)) {
            $result[] = ['type' => 'separator'];
        }
        foreach ($singles as $data) {
            $result[] = array_merge(['type' => 'single'], $data);
        }

        $this->timelinePositions = $result;
    }

    private function computeScale(): void
    {
        $slots = array_fill(0, 96, false);

        foreach ($this->bookings as $booking) {
            $from = $this->timeToMinutes($booking->from);
            $to = $this->timeToMinutes($booking->to);
            for ($m = $from; $m < $to; $m += 15) {
                $slot = (int) ($m / 15);
                if ($slot < 96) {
                    $slots[$slot] = true;
                }
            }
        }

        $hours = [];
        for ($h = 0; $h < 24; $h++) {
            $active = false;
            for ($s = 0; $s < 4; $s++) {
                if ($slots[$h * 4 + $s]) {
                    $active = true;
                    break;
                }
            }
            $hours[$h] = $active;
        }

        $activeWeight = 1.0;
        $inactiveWeight = 1.0 / 6.0;

        $totalWeight = 0;
        $weights = [];
        for ($h = 0; $h < 24; $h++) {
            $w = $hours[$h] ? $activeWeight : $inactiveWeight;
            $weights[$h] = $w;
            $totalWeight += $w;
        }

        $scale = [];
        $cumulative = 0;
        for ($h = 0; $h < 24; $h++) {
            for ($m = 0; $m < 60; $m++) {
                $minute = $h * 60 + $m;
                $frac = $m / 60;
                $scale[$minute] = round((($cumulative + $frac * $weights[$h]) / $totalWeight) * 100, 4);
            }
            $cumulative += $weights[$h];
        }

        $scale[1440] = 100.0;

        $this->timelineScale = $scale;
    }

    private function scalePos(int $minute): float
    {
        if ($minute <= 0) {
            return 0.0;
        }
        if ($minute >= 1440) {
            return 100.0;
        }

        return $this->timelineScale[$minute] ?? 0;
    }

    private function scaleWidth(int $fromMin, int $toMin): float
    {
        return max(round($this->scalePos($toMin) - $this->scalePos($fromMin), 2), 0.3);
    }

    public function getTimelineHours(): array
    {
        $slots = array_fill(0, 96, false);

        foreach ($this->bookings as $booking) {
            $from = $this->timeToMinutes($booking->from);
            $to = $this->timeToMinutes($booking->to);
            for ($m = $from; $m < $to; $m += 15) {
                $slot = (int) ($m / 15);
                if ($slot < 96) {
                    $slots[$slot] = true;
                }
            }
        }

        $hours = [];
        $gapStart = null;

        for ($h = 0; $h < 24; $h++) {
            $hasActivity = false;
            for ($m = 0; $m < 60; $m += 15) {
                $slot = ($h * 4) + ($m / 15);
                if ($slots[$slot]) {
                    $hasActivity = true;
                }
            }

            if ($hasActivity) {
                if ($gapStart !== null) {
                    $gapHours = $h - $gapStart;
                    if ($gapHours >= 3) {
                        $gapMin = $gapStart * 60;
                        $gapEnd = $h * 60;
                        $hours[] = [
                            'type' => 'gap',
                            'label' => sprintf('%02d:00 – %02d:00', $gapStart, $h),
                            'hour' => $gapStart,
                            'hours' => $gapHours,
                            'scale_left' => $this->scalePos($gapMin),
                            'scale_width' => $this->scaleWidth($gapMin, $gapEnd),
                        ];
                    } else {
                        for ($gh = $gapStart; $gh < $h; $gh++) {
                            $hMin = $gh * 60;
                            $hours[] = [
                                'type' => 'hour',
                                'hour' => $gh,
                                'scale_left' => $this->scalePos($hMin),
                            ];
                        }
                    }
                    $gapStart = null;
                }
                $hMin = $h * 60;
                $hours[] = [
                    'type' => 'hour',
                    'hour' => $h,
                    'scale_left' => $this->scalePos($hMin),
                ];
            } else {
                if ($gapStart === null) {
                    $gapStart = $h;
                }
            }
        }

        if ($gapStart !== null) {
            $gapHours = 24 - $gapStart;
            if ($gapHours >= 3) {
                $gapMin = $gapStart * 60;
                $hours[] = [
                    'type' => 'gap',
                    'label' => sprintf('%02d:00 – 00:00', $gapStart),
                    'hour' => $gapStart,
                    'hours' => $gapHours,
                    'scale_left' => $this->scalePos($gapMin),
                    'scale_width' => $this->scaleWidth($gapMin, 1440),
                ];
            } else {
                for ($gh = $gapStart; $gh < 24; $gh++) {
                    $hMin = $gh * 60;
                    $hours[] = [
                        'type' => 'hour',
                        'hour' => $gh,
                        'scale_left' => $this->scalePos($hMin),
                    ];
                }
            }
        }

        return $hours;
    }

    private function buildTimeClusters(array $positions): array
    {
        $all = [];
        foreach ($positions as $pos) {
            foreach ($pos['bookings'] as $b) {
                $all[] = $b;
            }
        }

        if (empty($all)) {
            return [];
        }

        usort($all, fn ($a, $b) => $this->timeToMinutes($a['from']) <=> $this->timeToMinutes($b['from']));

        $clusters = [];
        $current = [
            'from' => $all[0]['from'],
            'to' => $all[0]['to'],
            'count' => 1,
            'left_pct' => $all[0]['left_pct'],
            'right_pct' => $all[0]['left_pct'] + $all[0]['width_pct'],
            'members' => [$all[0]['member']?->cid ?? $all[0]['member']['cid'] ?? null],
        ];

        for ($i = 1; $i < count($all); $i++) {
            $b = $all[$i];
            $memberKey = $b['member']?->cid ?? $b['member']['cid'] ?? null;
            if ($this->timeToMinutes($b['from']) <= $this->timeToMinutes($current['to'])) {
                $current['to'] = $current['to'] > $b['to'] ? $current['to'] : $b['to'];
                $current['right_pct'] = max($current['right_pct'], $b['left_pct'] + $b['width_pct']);
                $current['count']++;
                if ($memberKey !== null && ! in_array($memberKey, $current['members'], true)) {
                    $current['members'][] = $memberKey;
                }
            } else {
                $cls = [
                    'from' => $current['from'],
                    'to' => $current['to'],
                    'count' => $current['count'],
                    'left_pct' => $current['left_pct'],
                    'right_pct' => $current['right_pct'],
                    'memberCount' => count($current['members']),
                ];
                $cls['width_pct'] = max(round($cls['right_pct'] - $cls['left_pct'], 2), 0.5);
                $clusters[] = $cls;
                $current = [
                    'from' => $b['from'],
                    'to' => $b['to'],
                    'count' => 1,
                    'left_pct' => $b['left_pct'],
                    'right_pct' => $b['left_pct'] + $b['width_pct'],
                    'members' => [$memberKey],
                ];
            }
        }
        $cls = [
            'from' => $current['from'],
            'to' => $current['to'],
            'count' => $current['count'],
            'left_pct' => $current['left_pct'],
            'right_pct' => $current['right_pct'],
            'memberCount' => count($current['members']),
        ];
        $cls['width_pct'] = max(round($cls['right_pct'] - $cls['left_pct'], 2), 0.5);
        $clusters[] = $cls;

        return $clusters;
    }

    private function timeToMinutes(string $time): int
    {
        $parts = explode(':', $time);

        return (int) $parts[0] * 60 + (int) $parts[1];
    }

    public function createBooking(array $data): void
    {
        if (! auth()->check()) {
            $this->dispatch('booking-error', message: 'You must be logged in to create a booking.');

            return;
        }

        if (auth()->user()->is_banned) {
            $this->dispatch('booking-error', message: 'Your account is not permitted to create bookings.');

            return;
        }

        $positionId = ! empty($data['position_id']) ? (int) $data['position_id'] : null;
        $customCallsign = ! empty($data['custom_callsign']) ? $data['custom_callsign'] : null;

        if (! $positionId && ! $customCallsign) {
            $this->dispatch('booking-error', message: 'Please select a position or enter a custom callsign.');

            return;
        }

        if (! empty($data['starts_at'])) {
            $startsAt = Carbon::parse($data['starts_at']);

            if ($startsAt->isPast()) {
                $this->dispatch('booking-error', message: 'Bookings cannot start in the past.');

                return;
            }

            if (! empty($data['ends_at'])) {
                $endsAt = Carbon::parse($data['ends_at']);

                if ($startsAt->equalTo($endsAt)) {
                    $this->dispatch('booking-error', message: 'Booking length cannot be zero minutes.');

                    return;
                }
            }
        }

        try {
            app(BookingService::class)->create([
                'position_id' => $positionId,
                'member_id' => auth()->id(),
                'type' => Booking::TYPE_STANDARD,
                'starts_at' => Carbon::parse($data['starts_at']),
                'ends_at' => Carbon::parse($data['ends_at']),
            ]);
            $this->refreshData();
            $this->dispatch('booking-created');
        } catch (RuntimeException $e) {
            $this->dispatch('booking-warning', message: $e->getMessage());
        } catch (InvalidArgumentException $e) {
            $this->dispatch('booking-error', message: $e->getMessage());
        }
    }

    public function cancelBooking(array $data): void
    {
        if (! auth()->check()) {
            $this->dispatch('booking-error', message: 'You must be logged in to cancel a booking.');

            return;
        }

        if (auth()->user()->is_banned) {
            $this->dispatch('booking-error', message: 'Your account is not permitted to modify bookings.');

            return;
        }

        $coreId = ! empty($data['id']) ? (int) $data['id'] : null;
        $ctsId = ! empty($data['cts_booking_id']) ? (int) $data['cts_booking_id'] : null;

        $core = null;

        if ($coreId !== null) {
            $core = Booking::findOrFail($coreId);
            $ctsId = $core->cts_booking_id !== null ? (int) $core->cts_booking_id : null;
            $isStandard = $core->type === Booking::TYPE_STANDARD;
            $memberId = $core->member_id;
            $endsAt = $core->ends_at;
        } elseif ($ctsId !== null) {
            $cts = CtsBooking::find($ctsId);

            if (! $cts) {
                $this->dispatch('booking-error', message: 'Booking not found.');

                return;
            }

            $core = Booking::where('cts_booking_id', $ctsId)->first();

            $isStandard = $cts->type === 'BK';
            $memberId = (int) $cts->member_id;
            $endsAt = $this->ctsEndsAt($cts);
        } else {
            $this->dispatch('booking-error', message: 'Booking not found.');

            return;
        }

        if (! $isStandard) {
            $this->dispatch('booking-error', message: 'Only standard bookings can be cancelled here.');

            return;
        }

        if ($memberId !== auth()->id()) {
            $this->dispatch('booking-error', message: 'You can only cancel your own bookings.');

            return;
        }

        if ($endsAt->isPast()) {
            $this->dispatch('booking-error', message: 'You cannot delete a booking that has already ended.');

            return;
        }

        if ($ctsId !== null) {
            app(BookingService::class)->cancelCtsBooking($ctsId, $core);
        } else {
            app(BookingService::class)->delete($core);
        }

        $this->refreshData();
        $this->dispatch('booking-deleted');
    }

    private function ctsEndsAt(CtsBooking $cts): Carbon
    {
        $from = substr((string) $cts->from, 0, 5);
        $to = substr((string) $cts->to, 0, 5);
        $end = Carbon::parse(Carbon::parse($cts->date)->format('Y-m-d').' '.$to);

        if ($to <= $from) {
            $end->addDay();
        }

        return $end;
    }
}
