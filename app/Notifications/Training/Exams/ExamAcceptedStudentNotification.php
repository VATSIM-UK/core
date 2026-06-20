<?php

namespace App\Notifications\Training\Exams;

use App\Enums\EmailType;
use App\Models\Cts\ExamBooking;
use App\Notifications\Contracts\HasEmailType;
use App\Services\IcsService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamAcceptedStudentNotification extends Notification implements HasEmailType
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private ExamBooking $examBooking) {}

    public function getEmailType(): EmailType
    {
        return EmailType::ExamAccepted;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Load relationships to ensure they're available
        $examBooking = $this->examBooking->load(['student', 'examiners.primaryExaminer', 'examiners.secondaryExaminer']);

        $examType = $examBooking->exam;
        $position = $examBooking->position_1 ?? 'N/A';
        $examDateTime = $examBooking->startDate;
        $primaryExaminer = $examBooking->examiners?->primaryExaminer?->account?->name ?? 'TBD';

        $icsContent = IcsService::generate(
            uid: "exam-{$examBooking->id}@vatsim.uk",
            summary: "Practical Exam - {$examType}",
            description: "Exam Type: {$examType}\nPosition: {$position}\nPrimary Examiner: {$primaryExaminer}\n\nPlease ensure you are prepared and on time for your exam. Most importantly - good luck!",
            start: Carbon::parse("{$examBooking->taken_date} {$examBooking->taken_from}"),
            end: Carbon::parse("{$examBooking->taken_date} {$examBooking->taken_to}"),
            location: $position,
        );

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject("Your {$examType} Practical Exam has been Accepted")
            ->view('emails.training.exams.exam_accepted_student', [
                'recipient' => $notifiable,
                'examBooking' => $examBooking,
                'examType' => $examType,
                'position' => $position,
                'examDateTime' => $examDateTime,
                'primaryExaminer' => $primaryExaminer,
            ])
            ->attachData($icsContent, 'event.ics', ['mime' => 'text/calendar']);
    }
}
