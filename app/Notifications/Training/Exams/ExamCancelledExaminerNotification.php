<?php

namespace App\Notifications\Training\Exams;

use App\Models\Cts\ExamBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamCancelledExaminerNotification extends Notification
{
    use Queueable;

    public function __construct(
        private ExamBooking $examBooking,
        private string $reason,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->examBooking->loadMissing('student');

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject("{$this->examBooking->exam} Practical Exam Cancelled")
            ->view('emails.training.exams.exam_cancelled_examiner', [
                'recipient' => $notifiable,
                'examBooking' => $this->examBooking,
                'reason' => $this->reason,
            ]);
    }
}
