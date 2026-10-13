<?php

namespace App\Notifications\Training;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SeminarTheoryExamReminderNotification extends Notification
{
    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('OBS > S1 Seminar - Theory Exam Required')
            ->view('emails.training.seminar_theory_exam_reminder', [
                'recipient' => $notifiable,
            ]);
    }
}
