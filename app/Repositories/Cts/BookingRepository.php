<?php

declare(strict_types=1);

namespace App\Repositories\Cts;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Cts\Event;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member as CtsMember;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingRepository
{
    private const TYPE_MAP = [
        Booking::TYPE_STANDARD => 'BK',
        Booking::TYPE_EXAM => 'EX',
        Booking::TYPE_MENTORING => 'ME',
        Booking::TYPE_EVENT => 'EV',
        Booking::TYPE_GROUP_SEMINAR => 'GS',
    ];

    public function getBookings(Carbon $date, bool $hideEndedTrainingSessions = false): Collection
    {
        return $this->getBookingsForRange($date, $date, $hideEndedTrainingSessions)
            ->get($date->toDateString(), collect());
    }

    /**
     * Same merge as getBookings(), batched across a date range.
     *
     * @return Collection<string, Collection<int, object>> keyed by Y-m-d date string
     */
    public function getBookingsForRange(Carbon $start, Carbon $end, bool $hideEndedTrainingSessions = false): Collection
    {
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();

        $core = Booking::whereDate('starts_at', '>=', $startDate)
            ->whereDate('starts_at', '<=', $endDate)
            ->with('member', 'position', 'ctsBooking')
            ->with(['bookable' => fn ($morphTo) => $morphTo->morphWith([
                Session::class => ['mentor'],
                ExamBooking::class => ['examiners.primaryExaminer'],
            ])])
            ->orderBy('starts_at')
            ->get();

        $importedIds = $core->pluck('cts_booking_id')->filter()->map(fn ($id) => (int) $id)->values()->all();

        $ctsOnly = CtsBooking::query()
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->when(! empty($importedIds), fn ($q) => $q->whereNotIn('id', $importedIds))
            ->orderBy('date')
            ->orderBy('from')
            ->get();

        // Best-effort match of CTS positions to core positions; may fail for positions
        // that have no core counterpart, in which case the raw CTS position is used.
        $ctsCallsigns = $ctsOnly->pluck('position')->filter()->unique()->values();
        $ctsPositions = Position::whereIn('callsign', $ctsCallsigns)->get()->keyBy('callsign');

        $ctsMemberIds = $ctsOnly->pluck('member_id')->filter()->unique()->values();
        $ctsMembers = CtsMember::whereIn('id', $ctsMemberIds)->get()->keyBy('id');
        $ctsCids = $ctsMembers->pluck('cid')->filter()->unique()->values();
        $ctsAccounts = Account::whereIn('id', $ctsCids)->get()->keyBy('id');

        [$examLookup, $sessionLookup] = $this->resolveCtsOwnersBatch($ctsOnly);

        // Events live in the CTS events table (not cts.bookings), so they must be
        // pulled in separately or they never appear on the calendar.
        $events = Event::whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->orderBy('date')
            ->orderBy('from')
            ->get();

        return $this->formatBookings($core)
            ->concat($ctsOnly->map(fn (CtsBooking $c) => $this->formatCtsBooking($c, $ctsPositions, $ctsMembers, $ctsAccounts, $examLookup, $sessionLookup)))
            ->concat($events->map(fn (Event $event) => $this->formatEvent($event)))
            ->groupBy(fn (object $b) => $b->date)
            ->map(function (Collection $dayBookings, string $dateKey) use ($hideEndedTrainingSessions) {
                $isToday = $dateKey === Carbon::today()->toDateString();

                return $dayBookings
                    ->reject(fn (object $b) => $hideEndedTrainingSessions && $isToday && $this->trainingSessionHasEnded($b))
                    ->sortBy(fn (object $b) => $b->from)
                    ->values();
            });
    }

    private function trainingSessionHasEnded(object $booking): bool
    {
        if (! in_array($booking->type, ['ME', 'EX'], true)) {
            return false;
        }

        $end = Carbon::parse($booking->date.' '.$booking->to);

        if ($booking->to <= $booking->from) {
            $end->addDay();
        }

        return $end->isPast();
    }

    public function getTodaysBookings(): Collection
    {
        return $this->getBookings(Carbon::now());
    }

    public function getTodaysLiveAtcBookings(): Collection
    {
        $bookings = Booking::whereDate('starts_at', Carbon::now()->toDateString())
            ->liveAtc()
            ->with('member', 'position', 'ctsBooking')
            ->orderBy('starts_at')
            ->get();

        return $this->formatBookings($bookings);
    }

    public function getTodaysLiveAtcBookingsWithoutEvents(): Collection
    {
        $bookings = Booking::whereDate('starts_at', Carbon::now()->toDateString())
            ->liveAtc()
            ->notEvent()
            ->with('member', 'position', 'ctsBooking')
            ->orderBy('starts_at')
            ->get();

        return $this->formatBookings($bookings);
    }

    public function getMemberUpcomingBookings(Account $account): Collection
    {
        $today = Carbon::today();

        $core = Booking::where('member_id', $account->getKey())
            ->where('starts_at', '>=', $today)
            ->where('type', Booking::TYPE_STANDARD)
            ->with('member', 'position', 'ctsBooking', 'bookable')
            ->orderBy('starts_at')
            ->get();

        $ctsMember = CtsMember::where('cid', $account->getKey())->first();

        if ($ctsMember === null) {
            return $this->formatBookings($core)->values();
        }

        $importedIds = $core->pluck('cts_booking_id')->filter()->map(fn ($id) => (int) $id)->values()->all();

        $cts = CtsBooking::query()
            ->where('member_id', $ctsMember->getKey())
            ->whereDate('date', '>=', $today->toDateString())
            ->where('type', 'BK')
            ->when(! empty($importedIds), fn ($q) => $q->whereNotIn('id', $importedIds))
            ->orderBy('date')
            ->orderBy('from')
            ->get();

        $ctsPositions = Position::whereIn('callsign', $cts->pluck('position')->filter()->unique()->values())
            ->get()
            ->keyBy('callsign');

        $ctsMembers = collect([$ctsMember->getKey() => $ctsMember]);
        $ctsAccounts = Account::whereIn('id', [$account->getKey()])->get()->keyBy('id');

        // Type 'BK' only, so never exam/mentoring.
        return $this->formatBookings($core)
            ->concat($cts->map(fn (CtsBooking $c) => $this->formatCtsBooking($c, $ctsPositions, $ctsMembers, $ctsAccounts, collect(), collect())))
            ->sortBy(fn (object $b) => $b->date.' '.$b->from)
            ->values();
    }

    public function getUpcomingMentoringAndExamBookings(int $limit = 10): Collection
    {
        $bookings = Booking::whereIn('type', [Booking::TYPE_MENTORING, Booking::TYPE_EXAM])
            ->where('ends_at', '>=', Carbon::now())
            ->with('position', 'ctsBooking')
            ->with(['bookable' => fn ($morphTo) => $morphTo->morphWith([
                Session::class => ['mentor'],
                ExamBooking::class => ['examiners.primaryExaminer'],
            ])])
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();

        return $this->formatBookings($bookings)->values();
    }

    private function formatBookings(Collection $bookings): Collection
    {
        return $bookings->map(function (Booking $booking) {
            $type = self::TYPE_MAP[$booking->type] ?? 'BK';

            return $this->makeBooking(
                id: (string) $booking->id,
                source: 'core',
                ctsBookingId: $booking->cts_booking_id !== null ? (int) $booking->cts_booking_id : null,
                positionId: $booking->position_id,
                // The CTS booking is the source of truth for the callsign: training
                // positions may not exist in the core positions table, so prefer the
                // CTS position and only fall back to the core position relationship.
                positionCallsign: $booking->ctsBooking?->position ?? $booking->position?->callsign,
                date: $booking->starts_at->format('Y-m-d'),
                from: $booking->starts_at->format('H:i'),
                to: $booking->ends_at->format('H:i'),
                type: $type,
                member: $this->formatMember($this->resolveOwner($booking)),
            );
        });
    }

    private function resolveOwner(Booking $booking): ?Account
    {
        if ($booking->type === Booking::TYPE_EXAM) {
            return $booking->bookable instanceof ExamBooking
                ? $booking->bookable->loadMissing('examiners.primaryExaminer')->examiners?->primaryExaminer?->account
                : null;
        }

        if ($booking->type === Booking::TYPE_MENTORING) {
            return $booking->bookable instanceof Session
                ? $booking->bookable->loadMissing('mentor')->mentor?->account
                : null;
        }

        return $booking->member;
    }

    private function formatCtsBooking(CtsBooking $cts, Collection $positions, Collection $members, Collection $accounts, Collection $examLookup, Collection $sessionLookup): object
    {
        $type = (string) $cts->type;
        $position = $positions->get($cts->position);
        $member = $members->get((int) $cts->member_id);
        $account = $member !== null ? $accounts->get((int) $member->cid) : null;

        // For exams and mentoring the booking row keys on the student, but the owner
        // shown on the calendar is always the leading examiner / mentor. Resolve them
        // from the matching exam/session record; never fall back to the student.
        $owner = $this->resolveCtsOwner($cts, $account, $examLookup, $sessionLookup);

        return $this->makeBooking(
            id: null,
            source: 'cts',
            ctsBookingId: (int) $cts->id,
            positionId: $position?->id,
            positionCallsign: $cts->position,
            date: Carbon::parse($cts->date)->format('Y-m-d'),
            from: substr((string) $cts->from, 0, 5),
            to: substr((string) $cts->to, 0, 5),
            type: $type,
            member: $this->formatMember($owner),
        );
    }

    private function resolveCtsOwner(CtsBooking $cts, ?Account $fallback, Collection $examLookup, Collection $sessionLookup): ?Account
    {
        if ($cts->isExam()) {
            $exam = $examLookup->get($this->ctsOwnerKey((int) $cts->member_id, (string) $cts->date, (string) $cts->from, (string) $cts->position));

            return $exam?->examiners?->primaryExaminer?->account;
        }

        if ($cts->isMentoring()) {
            $session = $sessionLookup->get($this->ctsOwnerKey((int) $cts->member_id, (string) $cts->date, (string) $cts->from, (string) $cts->position));

            return $session?->mentor?->account;
        }

        return $fallback;
    }

    /**
     * @return array{0: Collection<string, ExamBooking>, 1: Collection<string, Session>}
     */
    private function resolveCtsOwnersBatch(Collection $ctsOnly): array
    {
        $examRows = $ctsOnly->filter(fn (CtsBooking $c) => $c->isExam());
        $mentoringRows = $ctsOnly->filter(fn (CtsBooking $c) => $c->isMentoring());

        $examLookup = $examRows->isEmpty() ? collect() : ExamBooking::where('taken', 1)
            ->whereIn('student_id', $examRows->pluck('member_id')->filter()->map(fn ($id) => (int) $id)->unique()->values())
            ->whereIn('taken_date', $examRows->pluck('date')->unique()->values())
            ->with('examiners.primaryExaminer')
            ->get()
            ->keyBy(fn (ExamBooking $e) => $this->ctsOwnerKey((int) $e->student_id, (string) $e->taken_date, (string) $e->taken_from, (string) $e->position_1));

        $sessionLookup = $mentoringRows->isEmpty() ? collect() : Session::where('taken', 1)
            ->whereIn('student_id', $mentoringRows->pluck('member_id')->filter()->map(fn ($id) => (int) $id)->unique()->values())
            ->whereIn('taken_date', $mentoringRows->pluck('date')->unique()->values())
            ->with('mentor')
            ->get()
            ->keyBy(fn (Session $s) => $this->ctsOwnerKey((int) $s->student_id, (string) $s->taken_date, (string) $s->taken_from, (string) $s->position));

        return [$examLookup, $sessionLookup];
    }

    private function ctsOwnerKey(int $studentId, string $date, string $from, string $position): string
    {
        return $studentId.'|'.$date.'|'.$from.'|'.$position;
    }

    private function formatEvent(Event $event): object
    {
        $booking = $this->makeBooking(
            id: (string) $event->id,
            source: 'event',
            ctsBookingId: null,
            positionId: null,
            positionCallsign: null,
            date: $event->date->format('Y-m-d'),
            from: substr((string) $event->from, 0, 5),
            to: substr((string) $event->to, 0, 5),
            type: 'EV',
            member: $this->formatMember(null),
        );

        $booking->event_name = $event->event;

        return $booking;
    }

    private function makeBooking(?string $id, string $source, ?int $ctsBookingId, ?int $positionId, ?string $positionCallsign, string $date, string $from, string $to, string $type, array $member): object
    {
        return (object) [
            'id' => $id,
            'source' => $source,
            'cts_booking_id' => $ctsBookingId,
            'position_id' => $positionId,
            'position' => $positionCallsign,
            'date' => $date,
            'from' => $from,
            'to' => $to,
            'type' => $type,
            'member' => $member,
        ];
    }

    /**
     * The calendar is public, so this carries only what a booking block renders.
     * Never the full name: it would be shipped to every visitor in the Livewire
     * snapshot, publishing a name-to-CID mapping the page never shows.
     */
    private function formatMember(?Account $account): array
    {
        if (! $account) {
            return ['cid' => '', 'display_name' => 'Unknown'];
        }

        $firstName = $account->name_preferred;
        $lastInitial = mb_substr($account->name_last, 0, 1).'.';

        return [
            'cid' => (string) $account->id,
            'display_name' => $firstName.' '.$lastInitial,
        ];
    }
}
