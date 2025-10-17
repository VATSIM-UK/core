<?php

namespace App\Notifications\Training\Exams;

use App\Models\Cts\ExamBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamAcceptedStudentNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private ExamBooking $examBooking) {}

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
            ]);
    }
}
