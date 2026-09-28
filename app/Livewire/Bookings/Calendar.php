<?php

declare(strict_types=1);

namespace App\Livewire\Bookings;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Cts\Member as CtsMember;
use App\Models\Roster;
use App\Repositories\Cts\BookingRepository;
use App\Repositories\Events\EventRepository;
use App\Services\BookingService;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
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
     * Legend for the timeline's booking blocks, in render order. Keyed by the
     * codes in BookingRepository::TYPE_MAP and must stay exhaustive over them: a
     * type with no entry here is a colour on the timeline nothing explains.
     *
     * Standard bookings and events carry no icon -- standard is the vast majority,
     * and events have their own labelled row.
     */
    public const TYPE_LEGEND = [
        'BK' => ['label' => 'Booking', 'colour' => 'bg-uknavy', 'icon' => null],
        'ME' => ['label' => 'Mentoring', 'colour' => 'bg-purple-700', 'icon' => 'heroicon-m-academic-cap'],
        'EX' => ['label' => 'Exam', 'colour' => 'bg-amber-800', 'icon' => 'heroicon-m-clipboard-document-check'],
        'GS' => ['label' => 'Group seminar', 'colour' => 'bg-orange-500', 'icon' => 'heroicon-m-user-group'],
        'EV' => ['label' => 'Event', 'colour' => 'bg-red-600', 'icon' => null],
    ];

    /**
     * Colours match VATSIM Radar's position colours so a controller recognises them.
     */
    public const POSITION_TYPE_BADGES = [
        'DEL' => ['letter' => 'D', 'colour' => 'bg-[#458CFF]'],
        'GND' => ['letter' => 'G', 'colour' => 'bg-[#4A9C25]'],
        'TWR' => ['letter' => 'T', 'colour' => 'bg-[#D32C00]'],
        'APP' => ['letter' => 'R', 'colour' => 'bg-[#EE7901]'],
    ];

    private const BADGE_ORDER = ['DEL', 'GND', 'TWR', 'APP'];

    /**
     * Upper bound on candidate positions considered for a single search, applied
     * before the per-position qualification filter.
     */
    private const POSITION_SEARCH_LIMIT = 50;

    /**
     * Narrowest the timeline track can get, in pixels: the min-w-[1708px] body
     * less the 10rem (140px at the app's 14px root) position column. Header label
     * widths are measured against this so nothing overlaps at any window size.
     */
    private const TIMELINE_TRACK_MIN_WIDTH = 1568;

    /**
     * Rendered widths, in pixels, of the header labels at text-[10px]: "00:00"
     * for an hour tick, "00:00 - 00:00" for a gap band, and "00h" for the short
     * form a band falls back to when the scale has squeezed it too narrow.
     *
     * Measured in the browser at 23px, 56px and 16px respectively, plus the
     * hour tick's 6px pl-1.5, then rounded up by roughly a third: the app asks
     * for Calibri first (app.scss) but clients without it fall back to Tahoma,
     * whose digits are appreciably wider.
     */
    private const HOUR_LABEL_WIDTH = 40;

    private const GAP_LABEL_WIDTH = 72;

    private const GAP_SHORT_LABEL_WIDTH = 20;

    public Carbon $selectedDate;

    public string $positionFilter = '';

    public array $timelinePositions = [];

    public array $events = [];

    public int $eventLaneCount = 1;

    public int $filterVersion = 0;

    public int $dataVersion = 0;

    public string $viewMode = 'day';

    public array $weekBookings = [];

    /**
     * Derived render state. Deliberately not public: these are large (the scale
     * alone is 1441 floats) and recomputing them is far cheaper than shipping
     * them to the browser and back inside the Livewire snapshot on every request.
     * $upcomingBookings and $upcomingMentoringExamBookings are private for a
     * different reason: each mentor/examiner entry carries a CID, which must
     * never be exposed in the public Livewire snapshot.
     */
    private Collection $bookings;

    private array $timelineScale = [];

    private Collection $upcomingBookings;

    private Collection $upcomingMentoringExamBookings;

    public function mount(?int $year = null, ?int $month = null): void
    {
        $this->selectedDate = Carbon::today();

        $isoWeek = request()->input('week');
        $this->viewMode = $isoWeek !== null ? 'week' : 'day';

        $bookingId = request()->input('booking_id');
        $booking = ctype_digit((string) $bookingId) ? Booking::find((int) $bookingId) : null;

        if ($booking) {
            $this->selectedDate = $booking->starts_at->copy()->startOfDay();
        } elseif ($isoWeek !== null) {
            $this->selectedDate = Carbon::today()->setISODate($year ?? $this->selectedDate->isoWeekYear(), (int) $isoWeek);
        } elseif ($year) {
            $day = request()->input('day', 1);
            $this->selectedDate = Carbon::create($year, $month ?? $this->selectedDate->month, (int) $day);
        }

        $this->timelinePositions = [];
        $this->refreshData();

        if ($booking) {
            $this->dispatch('scroll-to-booking', source: 'core', id: $booking->id, ctsBookingId: null, instant: true);
        }
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
            'upcomingBookings' => $this->upcomingBookings,
            'upcomingMentoringExamBookings' => $this->upcomingMentoringExamBookings,
            'typeLegend' => self::TYPE_LEGEND,
            'viewMode' => $this->viewMode,
            'weekBookings' => $this->weekBookings,
        ]);
    }

    private function refreshData(): void
    {
        $this->loadData();
        $this->dataVersion++;
    }

    // Day- and week-view state are mutually exclusive; building both on every
    // load would double the bookings query and clustering work for the inactive mode.
    private function loadData(): void
    {
        if ($this->viewMode === 'week') {
            $this->bookings = collect();
            $this->timelineScale = [];
            $this->timelinePositions = [];
            $this->events = [];
            $this->eventLaneCount = 1;
            $this->loadWeekBookings();
        } else {
            $this->getBookingsForDate($this->selectedDate);
            $this->computeScale();
            $this->buildTimeline();
            $this->weekBookings = [];
        }

        $this->upcomingBookings = auth()->check() && ! auth()->user()->is_banned
            ? app(BookingRepository::class)->getMemberUpcomingBookings(auth()->user())
            : collect();

        $this->upcomingMentoringExamBookings = app(BookingRepository::class)->getUpcomingMentoringAndExamBookings();
    }

    // Cast to plain arrays: this is a public Livewire property, and must
    // serialise cleanly into the page's snapshot.
    private function loadWeekBookings(): void
    {
        $weekStart = $this->weekWindowStart();
        $weekEnd = $weekStart->copy()->addDays(6);
        $filter = strtoupper($this->positionFilter);

        $bookingsByDate = app(BookingRepository::class)->getBookingsForRange($weekStart, $weekEnd, hideEndedTrainingSessions: true);
        $eventsByDate = app(EventRepository::class)->getEventsForRange($weekStart, $weekEnd)->groupBy('date');

        $this->weekBookings = collect(range(0, 6))
            ->map(fn (int $offset): Carbon => $weekStart->copy()->addDays($offset))
            ->mapWithKeys(function (Carbon $date) use ($bookingsByDate, $eventsByDate, $filter): array {
                $dateKey = $date->toDateString();

                $bookings = $bookingsByDate->get($dateKey, collect())
                    ->reject(fn (object $booking): bool => $booking->type === 'EV')
                    ->when($filter !== '', fn (Collection $c) => $c->filter(
                        fn (object $booking): bool => str_starts_with(strtoupper($booking->position ?? ''), $filter)
                    ));

                // A callsign filter excludes events -- they carry no callsign to match.
                $events = $filter === '' ? $eventsByDate->get($dateKey, collect()) : collect();

                $rows = $bookings->concat($events)
                    ->sortBy(fn (object $booking): string => $booking->from)
                    ->values()
                    ->map(fn (object $booking): array => (array) $booking)
                    ->all();

                return [$dateKey => $rows];
            })
            ->all();
    }

    // Monday of the ISO-8601 week containing $selectedDate.
    public function weekWindowStart(): Carbon
    {
        return $this->selectedDate->copy()->startOfWeek(Carbon::MONDAY);
    }

    // An EV row with a callsign is the controller's own booking during an
    // event, not the event itself, so it's rejected here.
    private function mergedBookingsFor(Carbon $date): Collection
    {
        return app(BookingRepository::class)
            ->getBookings($date, hideEndedTrainingSessions: true)
            ->reject(fn (object $booking): bool => $booking->type === 'EV')
            ->concat(app(EventRepository::class)->getEventsForDate($date))
            ->sortBy(fn (object $booking): string => $booking->from)
            ->values();
    }

    public function updatedPositionFilter(): void
    {
        $this->filterVersion++;
        $this->loadData();
    }

    public function jumpToDate(string $date, bool $push = true): void
    {
        $this->selectedDate = Carbon::parse($date);
        $this->refreshData();
        $this->syncHistory($push);
    }

    // Public for direct test assertions. Week mode carries ISO (year, week) only --
    // month/day segments are redundant once the week is known.
    public function historyUrl(): string
    {
        if ($this->viewMode === 'week') {
            $monday = $this->weekWindowStart();

            return route('site.bookings.calendar', ['year' => $monday->isoWeekYear()]).'?week='.$monday->isoWeek();
        }

        return route('site.bookings.calendar', [
            'year' => $this->selectedDate->year,
            'month' => $this->selectedDate->month,
        ]).'?day='.$this->selectedDate->day;
    }

    private function syncHistory(bool $push): void
    {
        $this->js(sprintf(
            "history.%s({}, '', %s)",
            $push ? 'pushState' : 'replaceState',
            json_encode($this->historyUrl())
        ));
    }

    // Separate from jumpToDate so a booking already on the visible date can be
    // scrolled to without the dataVersion bump that rebuilds the timeline.
    public function jumpToBooking(string $date, string $source, ?int $id = null, ?int $ctsBookingId = null): void
    {
        if (! $this->selectedDate->isSameDay(Carbon::parse($date))) {
            $this->jumpToDate($date);
        }

        $this->dispatch('scroll-to-booking', source: $source, id: $id, ctsBookingId: $ctsBookingId, instant: true);
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode === 'week' ? 'week' : 'day';

        if ($this->viewMode === 'day') {
            $this->selectedDate = Carbon::today();
        }

        $this->loadData();
        $this->syncHistory(true);
    }

    #[On('sync-from-location')]
    public function syncFromLocation(?int $year, ?int $month, ?int $day, ?int $week = null, ?int $bookingId = null): void
    {
        $this->viewMode = $week !== null ? 'week' : 'day';

        $booking = $bookingId ? Booking::find($bookingId) : null;

        $this->selectedDate = match (true) {
            $booking !== null => $booking->starts_at->copy()->startOfDay(),
            $week !== null => Carbon::today()->setISODate($year ?? now()->isoWeekYear(), $week),
            $year !== null => Carbon::create($year, $month ?? now()->month, $day ?? 1),
            default => Carbon::today(),
        };

        $this->refreshData();
    }

    public function viewBookingFromWeek(string $date, string $source, ?int $id = null, ?int $ctsBookingId = null): void
    {
        $this->viewMode = 'day';
        $this->jumpToBooking($date, $source, $id, $ctsBookingId);
        $this->syncHistory(true);
    }

    // A merged block has no single booking to scroll to -- just show its day.
    public function viewDayFromWeek(string $date): void
    {
        $this->viewMode = 'day';
        $this->jumpToDate($date);
    }

    /**
     * Merges same-aerodrome bookings whose times touch or overlap into one
     * compact block; different aerodromes are never merged. Events always
     * sort before bookings; within each group, sorted by start.
     *
     * @return list<array{label: string, from: string, to: string, count: int, type: string, id: ?int, source: ?string, cts_booking_id: ?int, startMin: int, raw: ?array<string, mixed>, positionCodes: list<string>, isOwn: bool}>
     */
    public function buildWeekDayBlocks(string $dateKey): array
    {
        $groups = [];
        foreach ($this->weekBookings[$dateKey] ?? [] as $booking) {
            $groups[$this->weekGroupKey($booking)][] = $booking;
        }

        $blocks = [];
        foreach ($groups as $groupKey => $groupBookings) {
            foreach ($this->clusterByTime($groupBookings) as $cluster) {
                $cluster['positionCodes'] = $this->sortBadgeCodes($cluster['positionCodes']);

                // Merged: show the aerodrome. Unmerged: show the full position name.
                $blocks[] = [
                    'label' => $cluster['count'] > 1 ? $groupKey : $cluster['label'],
                    'from' => $cluster['from'],
                    'to' => $cluster['to'],
                    'count' => $cluster['count'],
                    'type' => $cluster['type'],
                    'id' => $cluster['count'] === 1 ? $cluster['id'] : null,
                    'source' => $cluster['count'] === 1 ? $cluster['source'] : null,
                    'cts_booking_id' => $cluster['count'] === 1 ? $cluster['cts_booking_id'] : null,
                    'startMin' => $cluster['startMin'],
                    'raw' => $cluster['count'] === 1 ? $cluster['booking'] : null,
                    'positionCodes' => $cluster['positionCodes'],
                    'isOwn' => $cluster['isOwn'],
                ];
            }
        }

        usort($blocks, fn (array $a, array $b): int => [$a['type'] !== 'EV', $a['startMin']] <=> [$b['type'] !== 'EV', $b['startMin']]);

        return $blocks;
    }

    // ICAO prefix (or full callsign if not ICAO-shaped). Events always get
    // a unique key, since two different events are never "the same aerodrome".
    private function weekGroupKey(array $booking): string
    {
        if ($booking['type'] === 'EV') {
            return 'event:'.($booking['id'] ?? $booking['event_name'] ?? spl_object_id((object) $booking));
        }

        $callsign = $booking['position'] ?? 'Unknown';
        $prefix = explode('_', $callsign)[0] ?? '';

        return (strlen($prefix) === 4 && ctype_alpha($prefix)) ? $prefix : $callsign;
    }

    // Label for a block covering exactly one booking.
    private function weekGroupLabel(array $booking): string
    {
        if ($booking['type'] === 'EV') {
            return $booking['event_name'] ?? 'Event';
        }

        return $booking['position'] ?? 'Unknown';
    }

    private function isOwnBooking(array $booking): bool
    {
        return auth()->check() && ($booking['member']['cid'] ?? null) === (string) auth()->id();
    }

    private function positionBadgeCode(array $booking): ?string
    {
        if ($booking['type'] === 'EV') {
            return null;
        }

        return match (Position::inferTypeFromCallsign($booking['position'] ?? '')) {
            Position::TYPE_DELIVERY => 'DEL',
            Position::TYPE_GROUND => 'GND',
            Position::TYPE_TOWER => 'TWR',
            Position::TYPE_APPROACH => 'APP',
            default => null,
        };
    }

    /**
     * @param  list<string>  $codes
     * @return list<string>
     */
    private function sortBadgeCodes(array $codes): array
    {
        usort($codes, fn (string $a, string $b): int => array_search($a, self::BADGE_ORDER) <=> array_search($b, self::BADGE_ORDER));

        return $codes;
    }

    /**
     * @param  list<array<string, mixed>>  $bookings
     * @return list<array{from: string, to: string, count: int, type: string, label: string, id: ?int, source: ?string, cts_booking_id: ?int, startMin: int, endMin: int, booking: array<string, mixed>, positionCodes: list<string>, isOwn: bool}>
     */
    private function clusterByTime(array $bookings): array
    {
        $prepared = array_map(function (array $booking): array {
            $start = $this->timeToMinutes($booking['from']);
            $end = $this->timeToMinutes($booking['to']);

            // Overnight booking (end <= start): treat as running to midnight.
            return $booking + ['startMin' => $start, 'endMin' => $end > $start ? $end : 1440];
        }, $bookings);

        usort($prepared, fn (array $a, array $b): int => $a['startMin'] <=> $b['startMin']);

        $clusters = [];
        $current = null;

        foreach ($prepared as $booking) {
            if ($current !== null && $booking['startMin'] <= $current['endMin']) {
                $current['endMin'] = max($current['endMin'], $booking['endMin']);
                $current['count']++;
                $code = $this->positionBadgeCode($booking);
                if ($code !== null && ! in_array($code, $current['positionCodes'], true)) {
                    $current['positionCodes'][] = $code;
                }
                if ($this->isOwnBooking($booking)) {
                    $current['isOwn'] = true;
                }

                continue;
            }

            if ($current !== null) {
                $clusters[] = $current;
            }

            $current = [
                'startMin' => $booking['startMin'],
                'endMin' => $booking['endMin'],
                'count' => 1,
                'type' => $booking['type'],
                'label' => $this->weekGroupLabel($booking),
                'id' => $booking['id'] !== null ? (int) $booking['id'] : null,
                'source' => $booking['source'],
                'cts_booking_id' => $booking['cts_booking_id'] !== null ? (int) $booking['cts_booking_id'] : null,
                'booking' => $booking,
                'positionCodes' => array_filter([$this->positionBadgeCode($booking)]),
                'isOwn' => $this->isOwnBooking($booking),
            ];
        }

        if ($current !== null) {
            $clusters[] = $current;
        }

        return array_map(fn (array $cluster): array => $cluster + [
            'from' => sprintf('%02d:%02d', intdiv($cluster['startMin'], 60), $cluster['startMin'] % 60),
            'to' => sprintf('%02d:%02d', intdiv($cluster['endMin'], 60) % 24, $cluster['endMin'] % 60),
        ], $clusters);
    }

    public function getBookingsForDate(Carbon $date): void
    {
        $this->bookings = $this->mergedBookingsFor($date);
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
                    'event_name' => $booking->event_name ?? null,
                    'event_url' => $booking->event_url ?? null,
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
            uasort($positions, fn (array $a, array $b): int => [Position::inferTypeFromCallsign($a['callsign']), $a['callsign']]
                <=> [Position::inferTypeFromCallsign($b['callsign']), $b['callsign']]);
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
                    $hours[] = $this->gapMarker($gapStart, $h);
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
            $hours[] = $this->gapMarker($gapStart, 24);
        }

        return $hours;
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
     * A band covering an inactive stretch, however short. Every inactive hour is
     * squeezed to a sixth of an active one, so each one gets the band treatment
     * rather than being left as a narrow, unshaded hour tick that looks like an
     * ordinary hour. Where the band is too narrow for the full range it falls
     * back to the duration, so the compressed time is still stated.
     *
     * @return array<string, mixed>
     */
    private function gapMarker(int $fromHour, int $toHour): array
    {
        $fromMinute = $fromHour * 60;
        $toMinute = $toHour * 60;
        $width = $this->scaleWidth($fromMinute, $toMinute);
        $hours = $toHour - $fromHour;

        return [
            'type' => 'gap',
            'label' => sprintf("%02d:00 \u{2013} %02d:00", $fromHour, $toHour % 24),
            'short_label' => sprintf('%dh', $hours),
            'hour' => $fromHour,
            'hours' => $hours,
            'scale_left' => $this->scalePos($fromMinute),
            'scale_width' => $width,
            'show_label' => $this->labelFits($width, self::GAP_LABEL_WIDTH),
            'show_short_label' => $this->labelFits($width, self::GAP_SHORT_LABEL_WIDTH),
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
                $all[] = $b + ['callsign' => $pos['callsign']];
            }
        }

        if (empty($all)) {
            return [];
        }

        usort($all, fn ($a, $b) => $this->timeToMinutes($a['from']) <=> $this->timeToMinutes($b['from']));

        $firstCode = $this->positionBadgeCode(['position' => $all[0]['callsign'], 'type' => $all[0]['type']]);

        $clusters = [];
        $current = [
            'from' => $all[0]['from'],
            'to' => $all[0]['to'],
            'count' => 1,
            'left_pct' => $all[0]['left_pct'],
            'right_pct' => $all[0]['left_pct'] + $all[0]['width_pct'],
            'members' => [$all[0]['member']?->cid ?? $all[0]['member']['cid'] ?? null],
            'positionCodes' => array_filter([$firstCode]),
        ];

        for ($i = 1; $i < count($all); $i++) {
            $b = $all[$i];
            $memberKey = $b['member']?->cid ?? $b['member']['cid'] ?? null;
            $code = $this->positionBadgeCode(['position' => $b['callsign'], 'type' => $b['type']]);
            if ($this->timeToMinutes($b['from']) <= $this->timeToMinutes($current['to'])) {
                $current['to'] = $current['to'] > $b['to'] ? $current['to'] : $b['to'];
                $current['right_pct'] = max($current['right_pct'], $b['left_pct'] + $b['width_pct']);
                $current['count']++;
                if ($memberKey !== null && ! in_array($memberKey, $current['members'], true)) {
                    $current['members'][] = $memberKey;
                }
                if ($code !== null && ! in_array($code, $current['positionCodes'], true)) {
                    $current['positionCodes'][] = $code;
                }
            } else {
                $cls = [
                    'from' => $current['from'],
                    'to' => $current['to'],
                    'count' => $current['count'],
                    'left_pct' => $current['left_pct'],
                    'right_pct' => $current['right_pct'],
                    'memberCount' => count($current['members']),
                    'positionCodes' => $this->sortBadgeCodes($current['positionCodes']),
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
                    'positionCodes' => array_filter([$code]),
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
            'positionCodes' => $this->sortBadgeCodes($current['positionCodes']),
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

    private function isOnFifteenMinuteBoundary(Carbon $time): bool
    {
        return $time->second === 0 && $time->minute % 15 === 0;
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

        // Mandatory: every check in BookingService::create() is gated behind a
        // non-null position_id.
        $positionId = ! empty($data['position_id']) ? (int) $data['position_id'] : null;

        if (! $positionId) {
            $this->dispatch('booking-error', message: 'Please select a position.');

            return;
        }

        // Required, and string-typed: validating only when present would let a
        // caller skip the checks below by omitting the field.
        $startsAtInput = $data['starts_at'] ?? null;
        $endsAtInput = $data['ends_at'] ?? null;

        if (! is_string($startsAtInput) || ! is_string($endsAtInput) || $startsAtInput === '' || $endsAtInput === '') {
            $this->dispatch('booking-error', message: 'Please provide a start and end time.');

            return;
        }

        try {
            $startsAt = Carbon::parse($startsAtInput);
            $endsAt = Carbon::parse($endsAtInput);
        } catch (InvalidFormatException) {
            $this->dispatch('booking-error', message: 'Please provide a valid start and end time.');

            return;
        }

        if ($startsAt->isPast()) {
            $this->dispatch('booking-error', message: 'Bookings cannot start in the past.');

            return;
        }

        if (! $this->isOnFifteenMinuteBoundary($startsAt) || ! $this->isOnFifteenMinuteBoundary($endsAt)) {
            $this->dispatch('booking-error', message: 'Start and end times must be on 15-minute boundaries.');

            return;
        }

        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            $this->dispatch('booking-error', message: 'End time must be after start time.');

            return;
        }

        try {
            app(BookingService::class)->create([
                'position_id' => $positionId,
                'member_id' => auth()->id(),
                'type' => Booking::TYPE_STANDARD,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
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

            // member_id is a CTS-internal id, assigned independently of the CID
            // (see HasCTSAccount::generateCTSInternalID), so it has to be
            // translated before it can be compared with auth()->id().
            $ctsMember = CtsMember::find((int) $cts->member_id);

            if ($ctsMember === null) {
                $this->dispatch('booking-error', message: 'Booking not found.');

                return;
            }

            $isStandard = $cts->type === 'BK';
            $memberId = (int) $ctsMember->cid;
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
