<?php

namespace App\Enums;

enum EmailType: string
{
    // Student - Sessions
    case SessionAcceptedByMentor = 'session_accepted_by_mentor';
    case SessionCancelledByMentor = 'session_cancelled_by_mentor';
    case SessionRescheduledByMentor = 'session_rescheduled_by_mentor';

    // Student - Exams
    case ExamAccepted = 'exam_accepted';
    case ExamCancelled = 'exam_cancelled';

    // Mentor
    case MentorSessionConfirmation = 'mentor_session_confirmation';
    case MentorSessionCancelled = 'mentor_session_cancelled';
    case MentorSessionRescheduled = 'mentor_session_rescheduled';

    // Examiner
    case ExaminerExamAccepted = 'examiner_exam_accepted';
    case ExaminerExamCancelled = 'examiner_exam_cancelled';

    public function label(): string
    {
        return match ($this) {
            // Student - Sessions
            self::SessionAcceptedByMentor => 'Mentor accepts session',
            self::SessionCancelledByMentor => 'Mentor cancels session',
            self::SessionRescheduledByMentor => 'Mentor reschedules session',

            // Student - Exams
            self::ExamAccepted => 'Exam accepted',
            self::ExamCancelled => 'Exam cancelled',

            // Mentor
            self::MentorSessionConfirmation => 'Session confirmation',
            self::MentorSessionCancelled => 'Student cancels session',
            self::MentorSessionRescheduled => 'Session rescheduled',

            // Examiner
            self::ExaminerExamAccepted => 'Exam accepted confirmation',
            self::ExaminerExamCancelled => 'Candidate cancels exam',
        };
    }

    public function description(): string
    {
        return match ($this) {
            // Student - Sessions
            self::SessionAcceptedByMentor => 'Sent when a mentor accepts your session request',
            self::SessionCancelledByMentor => 'Sent when a mentor cancels your session',
            self::SessionRescheduledByMentor => 'Sent when a mentor reschedules your session',

            // Student - Exams
            self::ExamAccepted => 'Sent when an examiner accepts your exam request',
            self::ExamCancelled => 'Sent when your exam is cancelled',

            // Mentor
            self::MentorSessionConfirmation => 'Sent when a session you are mentoring is confirmed',
            self::MentorSessionCancelled => 'Sent when a student cancels their session',
            self::MentorSessionRescheduled => 'Sent when a session you are mentoring is rescheduled',

            // Examiner
            self::ExaminerExamAccepted => 'Sent when you accept an exam request',
            self::ExaminerExamCancelled => 'Sent when a candidate cancels their exam',
        };
    }

    public function category(): string
    {
        return match ($this) {
            self::SessionAcceptedByMentor,
            self::SessionCancelledByMentor,
            self::SessionRescheduledByMentor,
            self::ExamAccepted,
            self::ExamCancelled => 'Student',

            self::MentorSessionConfirmation,
            self::MentorSessionCancelled,
            self::MentorSessionRescheduled => 'Mentor',

            self::ExaminerExamAccepted,
            self::ExaminerExamCancelled => 'Examiner',
        };
    }

    public static function forCategory(string $category): array
    {
        return array_filter(
            self::cases(),
            fn (EmailType $type) => $type->category() === $category
        );
    }

    public static function categories(): array
    {
        return array_unique(array_map(
            fn (EmailType $type) => $type->category(),
            self::cases()
        ));
    }
}
