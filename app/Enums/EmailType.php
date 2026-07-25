<?php

namespace App\Enums;

enum EmailType: string
{
    // Student - Sessions
    case SessionAcceptedByMentor = 'session_accepted_by_mentor';
    case SessionCancelledByMentor = 'session_cancelled_by_mentor';
    case SessionRescheduledByMentor = 'session_rescheduled_by_mentor';
    case SessionCancelledByStudent = 'session_cancelled_by_student';

    // Student - Exams
    case ExamAccepted = 'exam_accepted';
    case ExamCancelled = 'exam_cancelled';

    // Student - Mentoring
    case SessionReallocated = 'session_reallocated';
    case MentoringReportFiled = 'mentoring_report_filed';

    // Mentor
    case MentorSessionConfirmation = 'mentor_session_confirmation';
    case MentorSessionReallocated = 'mentor_session_reallocated';
    case MentorSessionCancelled = 'mentor_session_cancelled';
    case MentorSessionRescheduled = 'mentor_session_rescheduled';

    // Endorsement
    case EndorsementRequestCreated = 'endorsement_request_created';

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
            self::SessionCancelledByStudent => 'Student cancels session',

            // Student - Exams
            self::ExamAccepted => 'Exam accepted',
            self::ExamCancelled => 'Exam cancelled',

            // Student - Mentoring
            self::SessionReallocated => 'Session reallocated',
            self::MentoringReportFiled => 'Mentoring report filed',

            // Mentor
            self::MentorSessionConfirmation => 'Session confirmation',
            self::MentorSessionReallocated => 'Session reallocated',
            self::MentorSessionCancelled => 'Student cancels session',
            self::MentorSessionRescheduled => 'Session rescheduled',

            // Endorsement
            self::EndorsementRequestCreated => 'Endorsement request created',

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
            self::SessionCancelledByStudent => 'Sent when you cancel your own session',
            self::SessionReallocated => 'Sent when a mentoring session is reallocated to a new mentor',
            self::MentoringReportFiled => 'Sent when a mentoring report has been filed for your session',

            // Student - Exams
            self::ExamAccepted => 'Sent when an examiner accepts your exam request',
            self::ExamCancelled => 'Sent when your exam is cancelled',

            // Mentor
            self::MentorSessionConfirmation => 'Sent when you accept a mentoring session',
            self::MentorSessionReallocated => 'Sent when a session you were mentoring has been reallocated',
            self::MentorSessionCancelled => 'Sent when a student cancels their session',
            self::MentorSessionRescheduled => 'Sent when a session you are mentoring is rescheduled',

            // Endorsement
            self::EndorsementRequestCreated => 'Sent when a new endorsement request is created for approval',

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
            self::SessionCancelledByStudent,
            self::ExamAccepted,
            self::ExamCancelled,
            self::SessionReallocated,
            self::MentoringReportFiled => 'Student',

            self::MentorSessionConfirmation,
            self::MentorSessionCancelled,
            self::MentorSessionRescheduled,
            self::MentorSessionReallocated => 'Mentor',

            self::ExaminerExamAccepted,
            self::ExaminerExamCancelled => 'Examiner',

            self::EndorsementRequestCreated => 'Staff',
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
