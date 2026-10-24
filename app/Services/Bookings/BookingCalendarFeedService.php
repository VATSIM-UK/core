<?php

declare(strict_types=1);

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member as CtsMember;
use App\Models\Cts\PracticalExaminers;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Services\IcsService;
use App\Support\Calendar\CalendarSequence;
use App\Support\Calendar\CalendarUid;
use App\Support\Calendar\IcsEvent;
use App\Support\MemberDisplayName;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class BookingCalendarFeedService
{
    private const TYPE_LABELS = [
        Booking::TYPE_STANDARD => 'ATC booking',
        Booking::TYPE_EXAM => 'Practical exam',
        Booking::TYPE_MENTORING => 'Mentoring session',
        Booking::TYPE_GROUP_SEMINAR => 'Group seminar',
    ];

    /**
     * Bookings the member owns, plus sessions they mentor and exams they examine.
     */
    public function forAccount(Account $account): string
    {
        return IcsService::calendar(
            name: 'VATSIM UK bookings',
            events: $this->events($account),
        );
    }

    /**
     * @return Collection<int, IcsEvent>
     */
    public function events(Account $account): Collection
    {
        $from = Carbon::now()->subDays((int) config('bookings.feed_past_days'));
        $to = Carbon::now()->addDays((int) config('bookings.feed_future_days'));

        $ctsMemberId = $this->ctsMemberId($account);
        $sessionIds = $this->mentoredSessionIds($ctsMemberId, $from, $to);
        $examIds = $this->examinedExamIds($ctsMemberId, $from, $to);

        $bookings = Booking::query()
            ->where(function (Builder $query) use ($account, $sessionIds, $examIds) {
                $query->where('member_id', $account->getKey());

                if ($sessionIds->isNotEmpty()) {
                    $query->orWhere(fn (Builder $query) => $query
                        ->where('bookable_type', Session::class)
                        ->whereIn('bookable_id', $sessionIds));
                }

                if ($examIds->isNotEmpty()) {
                    $query->orWhere(fn (Builder $query) => $query
                        ->where('bookable_type', ExamBooking::class)
                        ->whereIn('bookable_id', $examIds));
                }
            })
            ->where('starts_at', '>=', $from)
            ->where('starts_at', '<=', $to)
            ->with([
                'position',
                'ctsBooking',
                'bookable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                    Session::class => ['mentor', 'student'],
                    ExamBooking::class => ['examiners.primaryExaminer', 'student'],
                ]),
            ])
            ->orderBy('starts_at')
            ->get();

        $counterparts = $this->counterpartAccounts($bookings, $account);

        return $bookings
            ->map(fn (Booking $booking): IcsEvent => $this->toEvent($booking, $account, $counterparts))
            ->values();
    }

    private function toEvent(Booking $booking, Account $account, Collection $counterparts): IcsEvent
    {
        $position = $this->position($booking);
        $viewerIsInstructor = $this->isInstructor($booking, $account);

        return new IcsEvent(
            uid: CalendarUid::forBooking($booking),
            summary: $this->summary($booking, $position),
            startsAt: $booking->starts_at,
            endsAt: $booking->ends_at,
            description: $this->description(
                $position,
                $this->counterpart($booking, $viewerIsInstructor, $counterparts),
            ),
            location: $position ?? '',
            sequence: $booking->bookable instanceof Session ? CalendarSequence::forSession($booking->bookable) : 0,
        );
    }

    private function summary(Booking $booking, ?string $position): string
    {
        $label = self::TYPE_LABELS[$booking->type] ?? 'Booking';

        return $position === null ? $label : $position.' - '.$label;
    }

    private function description(?string $position, ?string $counterpart): string
    {
        $lines = [];

        if ($position !== null) {
            $lines[] = 'Position: '.$position;
        }

        if ($counterpart !== null) {
            $lines[] = $counterpart;
        }

        $lines[] = 'Manage your bookings: '.route('site.bookings.calendar');

        return implode("\n", $lines);
    }

    /**
     * Exam and mentoring bookings key on the student, so the viewer is the
     * instructor when they are not the booking's member.
     */
    private function isInstructor(Booking $booking, Account $account): bool
    {
        return $booking->type !== Booking::TYPE_STANDARD
            && (int) $booking->member_id !== (int) $account->getKey();
    }

    private function counterpart(Booking $booking, bool $viewerIsInstructor, Collection $counterparts): ?string
    {
        $member = $this->counterpartMember($booking, $viewerIsInstructor);

        if ($member === null) {
            return null;
        }

        $account = $counterparts->get((int) $member->cid);

        if (! $account instanceof Account) {
            return null;
        }

        $label = $viewerIsInstructor
            ? 'Student'
            : ($booking->bookable instanceof ExamBooking ? 'Examiner' : 'Mentor');

        return $label.': '.MemberDisplayName::abbreviated($account);
    }

    private function counterpartMember(Booking $booking, bool $viewerIsInstructor): ?CtsMember
    {
        if ($booking->type === Booking::TYPE_EXAM && $booking->bookable instanceof ExamBooking) {
            return $viewerIsInstructor
                ? $booking->bookable->student
                : $booking->bookable->examiners?->primaryExaminer;
        }

        if ($booking->type === Booking::TYPE_MENTORING && $booking->bookable instanceof Session) {
            return $viewerIsInstructor
                ? $booking->bookable->student
                : $booking->bookable->mentor;
        }

        return null;
    }

    /**
     * Resolve every counterpart account in one query instead of one per event.
     *
     * @param  Collection<int, Booking>  $bookings
     * @return Collection<int, Account>
     */
    private function counterpartAccounts(Collection $bookings, Account $viewer): Collection
    {
        $cids = $bookings
            ->map(fn (Booking $booking) => $this->counterpartMember(
                $booking,
                $this->isInstructor($booking, $viewer),
            )?->cid)
            ->filter()
            ->map(fn ($cid): int => (int) $cid)
            ->unique()
            ->values();

        if ($cids->isEmpty()) {
            return collect();
        }

        return Account::query()
            ->whereIn('id', $cids)
            ->get()
            ->keyBy(fn (Account $account): int => (int) $account->getKey());
    }

    /**
     * CTS is the source of truth for training callsigns.
     */
    private function position(Booking $booking): ?string
    {
        $callsign = $booking->ctsBooking?->position ?? $booking->position?->callsign;

        if (blank($callsign) && $booking->bookable instanceof ExamBooking) {
            $callsign = $booking->bookable->position_1;
        }

        if (blank($callsign) && $booking->bookable instanceof Session) {
            $callsign = $booking->bookable->position;
        }

        return blank($callsign) ? null : (string) $callsign;
    }

    private function ctsMemberId(Account $account): ?int
    {
        $id = CtsMember::query()->where('cid', $account->getKey())->value('id');

        return $id === null ? null : (int) $id;
    }

    /**
     * @return Collection<int, int>
     */
    private function mentoredSessionIds(?int $ctsMemberId, Carbon $from, Carbon $to): Collection
    {
        if ($ctsMemberId === null) {
            return collect();
        }

        return Session::query()
            ->where('mentor_id', $ctsMemberId)
            ->whereBetween('taken_date', [$from->toDateString(), $to->toDateString()])
            ->pluck('id');
    }

    /**
     * @return Collection<int, int>
     */
    private function examinedExamIds(?int $ctsMemberId, Carbon $from, Carbon $to): Collection
    {
        if ($ctsMemberId === null) {
            return collect();
        }

        return PracticalExaminers::query()
            ->where(fn (Builder $query) => $query
                ->where('senior', $ctsMemberId)
                ->orWhere('other', $ctsMemberId)
                ->orWhere('trainee', $ctsMemberId))
            ->whereIn('examid', function ($query) use ($from, $to) {
                $query->select('id')
                    ->from('exam_book')
                    ->whereBetween('taken_date', [$from->toDateString(), $to->toDateString()]);
            })
            ->pluck('examid');
    }
}
