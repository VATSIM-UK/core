<?php

namespace App\Notifications\Training\Exams;

use App\Enums\EmailType;
use App\Models\Cts\ExamBooking;
use App\Notifications\Contracts\HasEmailType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamCancelledStudentNotification extends Notification implements HasEmailType
{
    use Queueable;

    public function __construct(
        private ExamBooking $examBooking,
    ) {}

    public function getEmailType(): EmailType
    {
        return EmailType::ExamCancelled;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject("{$this->examBooking->exam} Practical Exam Cancelled")
            ->view('emails.training.exams.exam_cancelled_student', [
                'recipient' => $notifiable,
                'examBooking' => $this->examBooking,
            ]);
    }
}
