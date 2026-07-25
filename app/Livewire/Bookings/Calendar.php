<?php

declare(strict_types=1);

namespace App\Livewire\Bookings;

use App\Models\Atc\Position;
use App\Models\Booking;
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

    public function mount(): void
    {
        $this->selectedDate = Carbon::today();
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
        ]);
    }

    private function refreshData(): void
    {
        $this->getBookingsForDate($this->selectedDate);
        $this->getQualifiedPositions();
        $this->buildTimeline();
    }

    public function goToToday(): void
    {
        $this->selectedDate = Carbon::today();
        $this->refreshData();
    }

    public function goToDate(string $date): void
    {
        $this->selectedDate = Carbon::parse($date);
        $this->refreshData();
    }

    public function goToNextDay(): void
    {
        $this->selectedDate = $this->selectedDate->copy()->addDay();
        $this->refreshData();
    }

    public function goToPreviousDay(): void
    {
        $this->selectedDate = $this->selectedDate->copy()->subDay();
        $this->refreshData();
    }

    public function updatedPositionFilter(): void
    {
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
        $scale = $this->computeScale();

        foreach ($this->bookings as $booking) {
            $callsign = $booking->position ?? 'Unknown';

            if ($this->positionFilter !== '' && ! str_starts_with(strtoupper($callsign), strtoupper($this->positionFilter))) {
                continue;
            }

            $start = $this->timeToMinutes($booking->from);
            $end = $this->timeToMinutes($booking->to);

            $left = $this->minuteToPct($scale, $start);
            $right = $this->minuteToPct($scale, $end);

            $bookingData = [
                'id' => (string) $booking->id,
                'from' => $booking->from,
                'to' => $booking->to,
                'left_pct' => $left,
                'width_pct' => max($right - $left, 0.1),
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

        // Merge in qualified positions that have no bookings yet
        foreach ($this->qualifiedPositions as $positionId => $callsign) {
            if ($this->positionFilter !== '' && ! str_starts_with(strtoupper($callsign), strtoupper($this->positionFilter))) {
                continue;
            }

            $parts = explode('_', $callsign);
            $prefix = $parts[0] ?? '';
            $isIcao = strlen($prefix) === 4 && ctype_alpha($prefix);

            if ($isIcao) {
                if (! isset($groups[$prefix][$callsign])) {
                    if (! isset($groups[$prefix])) {
                        $groups[$prefix] = [];
                    }
                    $groups[$prefix][$callsign] = [
                        'callsign' => $callsign,
                        'position_id' => (int) $positionId,
                        'bookings' => [],
                    ];
                }
            } else {
                if (! isset($singles[$callsign])) {
                    $singles[$callsign] = [
                        'callsign' => $callsign,
                        'position_id' => (int) $positionId,
                        'bookings' => [],
                    ];
                }
            }
        }

        $result = [];

        ksort($groups);
        foreach ($groups as $icao => $positions) {
            ksort($positions);
            $result[] = [
                'type' => 'group',
                'icao' => $icao,
                'positions' => array_values($positions),
                'clusters' => $this->buildTimeClusters($positions),
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

    private function computeScale(): array
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

        $totalWeight = 0;
        $weights = [];
        for ($h = 0; $h < 24; $h++) {
            $w = $hours[$h] ? 1.0 : 0.25;
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

        return $scale;
    }

    private function minuteToPct(array $scale, int $minute): float
    {
        return $scale[$minute] ?? 0;
    }

    public function getTimelineHours(): array
    {
        $scale = $this->computeScale();
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
                        $hours[] = [
                            'type' => 'gap',
                            'label' => sprintf('%02d:00 – %02d:00', $gapStart, $h),
                            'left_pct' => $this->minuteToPct($scale, $gapStart * 60),
                            'width_pct' => round($this->minuteToPct($scale, $h * 60) - $this->minuteToPct($scale, $gapStart * 60), 4),
                        ];
                    } else {
                        for ($gh = $gapStart; $gh < $h; $gh++) {
                            $hours[] = $this->hourEntry($scale, $gh);
                        }
                    }
                    $gapStart = null;
                }
                $hours[] = $this->hourEntry($scale, $h);
            } else {
                if ($gapStart === null) {
                    $gapStart = $h;
                }
            }
        }

        if ($gapStart !== null) {
            $gapHours = 24 - $gapStart;
            if ($gapHours >= 3) {
                $hours[] = [
                    'type' => 'gap',
                    'label' => sprintf('%02d:00 – 00:00', $gapStart),
                    'left_pct' => $this->minuteToPct($scale, $gapStart * 60),
                    'width_pct' => round(100 - $this->minuteToPct($scale, $gapStart * 60), 4),
                ];
            } else {
                for ($gh = $gapStart; $gh < 24; $gh++) {
                    $hours[] = $this->hourEntry($scale, $gh);
                }
            }
        }

        return $hours;
    }

    private function hourEntry(array $scale, int $h): array
    {
        return [
            'type' => 'hour',
            'hour' => $h,
            'left_pct' => $this->minuteToPct($scale, $h * 60),
        ];
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

        usort($all, fn ($a, $b) => $a['from'] <=> $b['from']);

        $clusters = [];
        $current = [
            'from' => $all[0]['from'],
            'to' => $all[0]['to'],
            'count' => 1,
            'left_pct' => $all[0]['left_pct'],
            'right_pct' => $all[0]['left_pct'] + $all[0]['width_pct'],
        ];

        for ($i = 1; $i < count($all); $i++) {
            $b = $all[$i];
            if ($this->timeToMinutes($b['from']) - $this->timeToMinutes($current['to']) <= 30) {
                $current['to'] = max($current['to'], $b['to']);
                $current['right_pct'] = max($current['right_pct'], $b['left_pct'] + $b['width_pct']);
                $current['count']++;
            } else {
                $clusters[] = $current;
                $current = [
                    'from' => $b['from'],
                    'to' => $b['to'],
                    'count' => 1,
                    'left_pct' => $b['left_pct'],
                    'right_pct' => $b['left_pct'] + $b['width_pct'],
                ];
            }
        }
        $clusters[] = $current;

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

    public function deleteBooking(int $id): void
    {
        $booking = Booking::findOrFail($id);

        if ($booking->member_id !== auth()->id()) {
            $this->dispatch('booking-error', message: 'You can only delete your own bookings.');

            return;
        }

        app(BookingService::class)->delete($booking);
        $this->refreshData();
        $this->dispatch('booking-deleted', id: $id);
    }
}
