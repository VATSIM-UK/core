<?php

declare(strict_types=1);

namespace App\Services\Bookings;

use App\Enums\CalendarViewer;
use App\Models\Booking;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member as CtsMember;
use App\Models\Cts\PracticalExaminers;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Services\IcsService;
use App\Support\Calendar\CalendarEventFactory;
use App\Support\Calendar\IcsEvent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class BookingCalendarFeedService
{
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

        $names = $this->participantNames($bookings);

        return $bookings
            ->map(fn (Booking $booking): IcsEvent => $this->toEvent($booking, $account, $names))
            ->values();
    }

    private function toEvent(Booking $booking, Account $account, Collection $names): IcsEvent
    {
        $viewer = $this->isInstructor($booking, $account) ? CalendarViewer::Instructor : CalendarViewer::Student;
        $nameOf = fn (CtsMember $member): ?string => $names->get((int) $member->cid);

        return match (true) {
            $booking->bookable instanceof Session => CalendarEventFactory::forSession($booking->bookable, $viewer, $nameOf),
            $booking->bookable instanceof ExamBooking => CalendarEventFactory::forExam($booking->bookable, $viewer, $nameOf),
            default => CalendarEventFactory::forBooking($booking),
        };
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

    /**
     * Resolve every participant's name in one query instead of one per event.
     *
     * @param  Collection<int, Booking>  $bookings
     * @return Collection<int, string>
     */
    private function participantNames(Collection $bookings): Collection
    {
        $cids = $bookings
            ->flatMap(fn (Booking $booking): array => match (true) {
                $booking->bookable instanceof Session => [$booking->bookable->student, $booking->bookable->mentor],
                $booking->bookable instanceof ExamBooking => [$booking->bookable->student, $booking->bookable->examiners?->primaryExaminer],
                default => [],
            })
            ->filter()
            ->map(fn (CtsMember $member): int => (int) $member->cid)
            ->unique()
            ->values();

        if ($cids->isEmpty()) {
            return collect();
        }

        return Account::query()
            ->whereIn('id', $cids)
            ->get()
            ->mapWithKeys(fn (Account $account): array => [(int) $account->getKey() => $account->name]);
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
