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
        private string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $examBooking = $this->examBooking->load(['student']);

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject("{$examBooking->exam} Practical Exam Cancelled")
            ->view('emails.training.exams.exam_cancelled_student', [
                'recipient' => $notifiable,
                'examBooking' => $examBooking,
                'examType' => $examBooking->exam,
                'position' => $examBooking->position_1,
                'takenDate' => $examBooking->taken_date,
                'takenFrom' => $examBooking->taken_from,
                'takenTo' => $examBooking->taken_to,
            ]);
    }
}
