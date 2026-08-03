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

    public function getBookings(Carbon $date): Collection
    {
        $core = Booking::whereDate('starts_at', $date->toDateString())
            ->with('member', 'position', 'ctsBooking', 'bookable')
            ->orderBy('starts_at')
            ->get();

        $importedIds = $core->pluck('cts_booking_id')->filter()->map(fn ($id) => (int) $id)->values()->all();

        $ctsOnly = CtsBooking::query()
            ->whereDate('date', $date->toDateString())
            ->when(! empty($importedIds), fn ($q) => $q->whereNotIn('id', $importedIds))
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

        // Events live in the CTS events table (not cts.bookings), so they must be
        // pulled in separately or they never appear on the calendar.
        $events = Event::whereDate('date', $date->toDateString())
            ->where(fn ($q) => $q->where('gone', 0)->orWhereNull('gone'))
            ->orderBy('from')
            ->get();

        return $this->formatBookings($core)
            ->concat($ctsOnly->map(fn (CtsBooking $c) => $this->formatCtsBooking($c, $ctsPositions, $ctsMembers, $ctsAccounts)))
            ->concat($events->map(fn (Event $event) => $this->formatEvent($event)))
            ->sortBy(fn (object $b) => $b->from)
            ->values();
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

    private function formatCtsBooking(CtsBooking $cts, Collection $positions, Collection $members, Collection $accounts): object
    {
        $type = (string) $cts->type;
        $position = $positions->get($cts->position);
        $member = $members->get((int) $cts->member_id);
        $account = $member !== null ? $accounts->get((int) $member->cid) : null;

        // For exams and mentoring the booking row keys on the student, but the owner
        // shown on the calendar is always the leading examiner / mentor. Resolve them
        // from the matching exam/session record; never fall back to the student.
        $owner = $this->resolveCtsOwner($cts, $account);

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

    private function resolveCtsOwner(CtsBooking $cts, ?Account $fallback): ?Account
    {
        if ($cts->isExam()) {
            $exam = ExamBooking::where('student_id', (int) $cts->member_id)
                ->where('taken', 1)
                ->where('taken_date', $cts->date)
                ->where('taken_from', $cts->from)
                ->where('position_1', $cts->position)
                ->first();

            return $exam?->loadMissing('examiners.primaryExaminer')->examiners?->primaryExaminer?->account;
        }

        if ($cts->isMentoring()) {
            $session = Session::where('student_id', (int) $cts->member_id)
                ->where('taken', 1)
                ->where('taken_date', $cts->date)
                ->where('taken_from', $cts->from)
                ->where('position', $cts->position)
                ->first();

            return $session?->loadMissing('mentor')->mentor?->account;
        }

        return $fallback;
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
            member: $this->formatMember($event->member?->account),
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

    private function formatMember(?Account $account): array
    {
        if (! $account) {
            return ['id' => '', 'cid' => '', 'name' => 'Unknown', 'display_name' => 'Unknown'];
        }

        $firstName = $account->name_first;
        $lastInitial = mb_substr($account->name_last, 0, 1).'.';

        return [
            'id' => (string) $account->id,
            'cid' => (string) $account->id,
            'name' => $account->name,
            'display_name' => $firstName.' '.$lastInitial,
        ];
    }
}
