<?php

declare(strict_types=1);

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Services\IcsService;
use App\Support\Calendar\CalendarUid;
use App\Support\Calendar\IcsEvent;
use App\Support\MemberDisplayName;
use Carbon\Carbon;
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
     * Exam and mentoring rows key on the student, so this is the student side of both.
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
        return Booking::query()
            ->where('member_id', $account->getKey())
            ->where('starts_at', '>=', Carbon::now()->subDays((int) config('bookings.feed_past_days')))
            ->where('starts_at', '<=', Carbon::now()->addDays((int) config('bookings.feed_future_days')))
            ->with([
                'position',
                'ctsBooking',
                'bookable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                    Session::class => ['mentor'],
                    ExamBooking::class => ['examiners.primaryExaminer'],
                ]),
            ])
            ->orderBy('starts_at')
            ->get()
            ->map(fn (Booking $booking): IcsEvent => $this->toEvent($booking))
            ->values();
    }

    private function toEvent(Booking $booking): IcsEvent
    {
        $position = $this->position($booking);

        return new IcsEvent(
            uid: CalendarUid::forBooking($booking),
            summary: $this->summary($booking, $position),
            startsAt: $booking->starts_at,
            endsAt: $booking->ends_at,
            description: $this->description($position, $this->counterpart($booking)),
            location: $position ?? '',
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

    private function counterpart(Booking $booking): ?string
    {
        if ($booking->type === Booking::TYPE_EXAM && $booking->bookable instanceof ExamBooking) {
            $account = $booking->bookable->examiners?->primaryExaminer?->account;

            return $account === null ? null : 'Examiner: '.MemberDisplayName::abbreviated($account);
        }

        if ($booking->type === Booking::TYPE_MENTORING && $booking->bookable instanceof Session) {
            $account = $booking->bookable->mentor?->account;

            return $account === null ? null : 'Mentor: '.MemberDisplayName::abbreviated($account);
        }

        return null;
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
}
