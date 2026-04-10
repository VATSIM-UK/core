<?php

namespace App\Notifications\Training\Exams;

use App\Models\Cts\ExamBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamCancelledStudentNotification extends Notification
{
    use Queueable;

    public function __construct(
        private ExamBooking $examBooking,
        private string $reason
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $examType = $this->examBooking->exam;
        $position = $this->examBooking->position_1;

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject("Cancellation Confirmation: {$examType} Practical Exam")
            ->view('emails.training.exams.exam_cancelled_student', [
                'recipient' => $notifiable,
                'examType' => $examType,
                'position' => $position,
                'reason' => $this->reason,
            ]);
    }
}
