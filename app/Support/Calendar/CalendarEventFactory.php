<?php

declare(strict_types=1);

namespace App\Support\Calendar;

use App\Enums\CalendarViewer;
use App\Filament\Training\Pages\Exam\Exams;
use App\Filament\Training\Pages\Mentor\Mentoring;
use App\Filament\Training\Pages\MyTraining\MyAvailability;
use App\Filament\Training\Pages\MyTraining\MyExams;
use App\Models\Booking;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use Carbon\Carbon;
use Closure;

final class CalendarEventFactory
{
    private const KIND_LABELS = [
        Booking::TYPE_STANDARD => 'ATC booking',
        Booking::TYPE_EXAM => 'Practical exam',
        Booking::TYPE_MENTORING => 'Mentoring session',
        Booking::TYPE_GROUP_SEMINAR => 'Group seminar',
    ];

    /**
     * @param  (Closure(Member): ?string)|null  $nameOf  Resolves a member's display name; defaults to their account name
     * @param  string|null  $note  Extra line shown before the manage link, e.g. a reschedule notice
     */
    public static function forSession(Session $session, CalendarViewer $viewer, ?Closure $nameOf = null, ?string $note = null): IcsEvent
    {
        $nameOf ??= self::accountName(...);
        [$start, $end] = self::times($session->taken_date, $session->taken_from, $session->taken_to);
        $position = self::filled($session->position);
        $url = $viewer === CalendarViewer::Student
            ? MyAvailability::getUrl(panel: 'training')
            : Mentoring::getUrl(panel: 'training');

        return new IcsEvent(
            uid: CalendarUid::forSession((int) $session->id),
            summary: self::summary(Booking::TYPE_MENTORING, $position),
            startsAt: $start,
            endsAt: $end,
            description: self::description([
                'Position' => $position,
                'Student' => $viewer === CalendarViewer::Instructor ? self::student($session->student, $nameOf) : null,
                'Mentor' => $session->mentor ? $nameOf($session->mentor) : null,
            ], $note, $url),
            location: $position ?? '',
            sequence: CalendarSequence::forSession($session),
            url: $url,
        );
    }

    /**
     * @param  (Closure(Member): ?string)|null  $nameOf  Resolves a member's display name; defaults to their account name
     * @param  string|null  $note  Extra line shown before the manage link
     */
    public static function forExam(ExamBooking $exam, CalendarViewer $viewer, ?Closure $nameOf = null, ?string $note = null): IcsEvent
    {
        $nameOf ??= self::accountName(...);
        [$start, $end] = self::times($exam->taken_date, $exam->taken_from, $exam->taken_to);
        $position = self::filled($exam->position_1);
        $examiner = $exam->examiners?->primaryExaminer;
        $url = $viewer === CalendarViewer::Student
            ? MyExams::getUrl(panel: 'training')
            : Exams::getUrl(panel: 'training');

        return new IcsEvent(
            uid: CalendarUid::forExam((int) $exam->id),
            summary: self::summary(Booking::TYPE_EXAM, $position),
            startsAt: $start,
            endsAt: $end,
            description: self::description([
                'Position' => $position,
                'Exam' => self::filled($exam->exam),
                'Student' => $viewer === CalendarViewer::Instructor ? self::student($exam->student, $nameOf) : null,
                'Examiner' => $examiner ? $nameOf($examiner) : null,
            ], $note, $url),
            location: $position ?? '',
            url: $url,
        );
    }

    /**
     * Bookings without a CTS session or exam behind them, such as standard ATC bookings.
     */
    public static function forBooking(Booking $booking): IcsEvent
    {
        $position = self::filled($booking->ctsBooking?->position ?? $booking->position?->callsign);
        $url = route('site.bookings.calendar', ['booking_id' => $booking->getKey()]);

        return new IcsEvent(
            uid: CalendarUid::forBooking($booking),
            summary: self::summary($booking->type, $position),
            startsAt: $booking->starts_at,
            endsAt: $booking->ends_at,
            description: self::description(['Position' => $position], null, $url),
            location: $position ?? '',
            url: $url,
        );
    }

    private static function summary(?string $type, ?string $position): string
    {
        $kind = self::KIND_LABELS[$type] ?? 'Booking';

        return $position === null ? $kind : "{$position} - {$kind}";
    }

    /**
     * @param  array<string, ?string>  $fields
     */
    private static function description(array $fields, ?string $note, string $url): string
    {
        $lines = collect($fields)
            ->filter(fn (?string $value): bool => filled($value))
            ->map(fn (string $value, string $label): string => "{$label}: {$value}")
            ->values();

        if (filled($note)) {
            $lines->push($note);
        }

        return $lines->push('', "Manage: {$url}")->implode("\n");
    }

    /**
     * @param  Closure(Member): ?string  $nameOf
     */
    private static function student(?Member $student, Closure $nameOf): ?string
    {
        if ($student === null) {
            return null;
        }

        $name = $nameOf($student);

        return filled($name) ? "{$name} ({$student->cid})" : (string) $student->cid;
    }

    private static function accountName(Member $member): ?string
    {
        return $member->account?->name;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private static function times(mixed $date, mixed $from, mixed $to): array
    {
        $day = Carbon::parse($date)->toDateString();
        $start = Carbon::parse($day.' '.Carbon::parse($from)->format('H:i:s'));
        $end = Carbon::parse($day.' '.Carbon::parse($to)->format('H:i:s'));

        if ($end->lte($start)) {
            $end->addDay();
        }

        return [$start, $end];
    }

    private static function filled(mixed $value): ?string
    {
        return blank($value) ? null : (string) $value;
    }
}
