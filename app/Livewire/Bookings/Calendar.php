<?php

declare(strict_types=1);

namespace App\Livewire\Bookings;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Roster;
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
    /**
     * Minimum length of a position search term. Almost every UK callsign starts
     * with "E", so anything shorter matches most of the position table and makes
     * the qualification check (which is per-position) prohibitively expensive.
     */
    public const POSITION_SEARCH_MIN_LENGTH = 3;

    /**
     * Upper bound on candidate positions considered for a single search, applied
     * before the per-position qualification filter.
     */
    private const POSITION_SEARCH_LIMIT = 50;

    /**
     * An inactive stretch of at least this many hours collapses into a single
     * labelled band instead of keeping one tick per hour.
     */
    private const GAP_COLLAPSE_HOURS = 3;

    /**
     * Narrowest the timeline track can get, in pixels: the min-w-[1680px] body
     * less the 8rem (112px at the app's 14px root) position column. Header label
     * widths are measured against this so nothing overlaps at any window size.
     */
    private const TIMELINE_TRACK_MIN_WIDTH = 1568;

    /**
     * Approximate rendered widths, in pixels, of the two header labels at
     * text-[10px], including their padding and a little slack: "00:00" for an
     * hour tick and "00:00 - 00:00" for a collapsed gap band.
     */
    private const HOUR_LABEL_WIDTH = 40;

    private const GAP_LABEL_WIDTH = 80;

    public Carbon $selectedDate;

    public string $positionFilter = '';

    public array $timelinePositions = [];

    public array $events = [];

    public int $eventLaneCount = 1;

    public int $filterVersion = 0;

    public int $dataVersion = 0;

    /**
     * Derived render state. Deliberately not public: these are large (the scale
     * alone is 1441 floats) and recomputing them is far cheaper than shipping
     * them to the browser and back inside the Livewire snapshot on every request.
     */
    private Collection $bookings;

    private array $timelineScale = [];

    public function mount(?int $year = null, ?int $month = null): void
    {
        $this->selectedDate = Carbon::today();

        if ($year) {
            $day = request()->input('day', 1);
            $this->selectedDate = Carbon::create($year, $month ?? $this->selectedDate->month, (int) $day);
        }

        $this->timelinePositions = [];
        $this->refreshData();
    }

    public function render()
    {
        // On a hydrated request nothing may have touched the derived state yet
        // (the private properties do not survive serialisation), so load it here.
        if (! isset($this->bookings)) {
            $this->loadData();
        }

        return view('livewire.bookings.calendar', [
            'timelinePositions' => $this->timelinePositions,
            'events' => $this->events,
            'eventLaneCount' => $this->eventLaneCount,
            'timelineHours' => $this->getTimelineHours(),
            'selectedDate' => $this->selectedDate,
            'timelineScale' => array_values($this->timelineScale),
        ]);
    }

    private function refreshData(): void
    {
        $this->loadData();
        $this->dataVersion++;
    }

    private function loadData(): void
    {
        $this->getBookingsForDate($this->selectedDate);
        $this->computeScale();
        $this->buildTimeline();
    }

    public function updatedPositionFilter(): void
    {
        $this->filterVersion++;
        $this->loadData();
    }

    public function getBookingsForDate(Carbon $date): void
    {
        $this->bookings = app(BookingRepository::class)->getBookings($date);
    }

    /**
     * Look up bookable positions for the current member on demand.
     *
     * The full qualified-position list used to be built on page load, which meant
     * a qualification check against every position in the table before anything
     * rendered. Searching narrows the candidate set to a handful of rows instead.
     *
     * @return list<array{id: string, callsign: string}>
     */
    public function searchPositions(string $query): array
    {
        $query = strtoupper(trim($query));

        if (mb_strlen($query) < self::POSITION_SEARCH_MIN_LENGTH) {
            return [];
        }

        $account = auth()->user();
        $roster = $account !== null ? Roster::firstWhere('account_id', $account->getKey()) : null;

        if ($roster === null) {
            return [];
        }

        return Position::real()
            ->where('callsign', 'like', '%'.addcslashes($query, '%_\\').'%')
            ->orderBy('callsign')
            ->limit(self::POSITION_SEARCH_LIMIT)
            ->get()
            ->filter(fn (Position $position): bool => (bool) $roster->accountCanControl($position))
            ->map(fn (Position $position): array => [
                'id' => (string) $position->id,
                'callsign' => $position->callsign,
            ])
            ->values()
            ->all();
    }

    public function buildTimeline(): void
    {
        $groups = [];
        $singles = [];
        $events = [];

        $filter = strtoupper($this->positionFilter);

        foreach ($this->bookings as $booking) {
            $isEvent = $booking->type === 'EV';
            $callsign = $booking->position ?? 'Unknown';

            // Events have no callsign, so a callsign search simply excludes them
            // rather than matching them against the "Unknown" placeholder.
            if ($filter !== '' && ($isEvent || ! str_starts_with(strtoupper($callsign), $filter))) {
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

            if ($isEvent) {
                // Events carry their name rather than a callsign, and it is the only
                // label the events row has to show. It is set by the repository as a
                // dynamic property, so it is absent on every other booking type.
                $events[] = $bookingData + [
                    'position' => $booking->position,
                    'event_name' => $booking->event_name ?? null,
                ];

                continue;
            }

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
            $posArray = array_values(array_map($this->assignLanes(...), $positions));
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
            $result[] = array_merge(['type' => 'single'], $this->assignLanes($data));
        }

        // Events share a single row, so they need lanes for the same reason
        // position bookings do. assignLanes also orders them by start time.
        $eventRow = $this->assignLanes(['bookings' => $events]);

        $this->events = $eventRow['bookings'];
        $this->eventLaneCount = $eventRow['laneCount'];
        $this->timelinePositions = $result;
    }

    /**
     * Give every booking in a row a vertical lane, so that bookings overlapping
     * in time can be stacked rather than drawn on top of one another.
     *
     * Greedy first fit over bookings ordered by start time: a booking takes the
     * lowest lane whose previous occupant has already finished, which is optimal
     * for interval graphs -- it never uses more lanes than the busiest instant
     * requires. Overlaps are meant to be rare, so most rows come back with one
     * lane and render exactly as they did before.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function assignLanes(array $row): array
    {
        $bookings = $row['bookings'];

        usort($bookings, fn (array $a, array $b): int => [$a['startMin'], $this->layoutEndMinute($a)]
            <=> [$b['startMin'], $this->layoutEndMinute($b)]);

        $laneEnds = [];

        foreach ($bookings as $index => $booking) {
            $lane = 0;
            while (isset($laneEnds[$lane]) && $laneEnds[$lane] > $booking['startMin']) {
                $lane++;
            }

            $laneEnds[$lane] = $this->layoutEndMinute($booking);
            $bookings[$index]['lane'] = $lane;
        }

        $row['bookings'] = $bookings;
        $row['laneCount'] = max(1, count($laneEnds));

        return $row;
    }

    /**
     * A booking whose end is not after its start runs past midnight. Only the
     * part inside the day being rendered can collide with anything on this row,
     * so for layout it occupies the remainder of the day.
     *
     * @param  array<string, mixed>  $booking
     */
    private function layoutEndMinute(array $booking): int
    {
        return $booking['endMin'] > $booking['startMin'] ? $booking['endMin'] : 1440;
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
                    $hours = array_merge($hours, $this->gapMarkers($gapStart, $h));
                    $gapStart = null;
                }

                $hours[] = $this->hourMarker($h);
            } else {
                if ($gapStart === null) {
                    $gapStart = $h;
                }
            }
        }

        if ($gapStart !== null) {
            $hours = array_merge($hours, $this->gapMarkers($gapStart, 24));
        }

        return $hours;
    }

    /**
     * Header markers covering an inactive stretch. A long gap collapses into a
     * single labelled band; a short one keeps its individual hour ticks, which
     * the scale has already compressed.
     *
     * @return list<array<string, mixed>>
     */
    private function gapMarkers(int $fromHour, int $toHour): array
    {
        if ($toHour - $fromHour >= self::GAP_COLLAPSE_HOURS) {
            return [$this->gapMarker($fromHour, $toHour)];
        }

        $markers = [];

        for ($hour = $fromHour; $hour < $toHour; $hour++) {
            $markers[] = $this->hourMarker($hour);
        }

        return $markers;
    }

    /**
     * @return array<string, mixed>
     */
    private function hourMarker(int $hour): array
    {
        $minute = $hour * 60;

        return [
            'type' => 'hour',
            'hour' => $hour,
            'scale_left' => $this->scalePos($minute),
            'show_label' => $this->labelFits($this->scaleWidth($minute, $minute + 60), self::HOUR_LABEL_WIDTH),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function gapMarker(int $fromHour, int $toHour): array
    {
        $fromMinute = $fromHour * 60;
        $toMinute = $toHour * 60;
        $width = $this->scaleWidth($fromMinute, $toMinute);

        return [
            'type' => 'gap',
            'label' => sprintf("%02d:00 \u{2013} %02d:00", $fromHour, $toHour % 24),
            'hour' => $fromHour,
            'hours' => $toHour - $fromHour,
            'scale_left' => $this->scalePos($fromMinute),
            'scale_width' => $width,
            'show_label' => $this->labelFits($width, self::GAP_LABEL_WIDTH),
        ];
    }

    /**
     * Header labels are placed as a percentage of the timeline track, so a
     * column compressed by the scale can be far narrower than the text it would
     * carry -- an inactive hour is a sixth of an active one. Drop the label when
     * it would not fit at the track's minimum width, leaving the tick in place,
     * rather than letting it spill over the neighbouring column.
     */
    private function labelFits(float $widthPct, int $labelWidth): bool
    {
        return $widthPct >= ($labelWidth / self::TIMELINE_TRACK_MIN_WIDTH) * 100;
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
