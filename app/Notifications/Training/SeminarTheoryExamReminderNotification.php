<?php

namespace App\Notifications\Training;

use App\Models\Training\Seminar\Seminar;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SeminarTheoryExamReminderNotification extends Notification
{
    public function __construct(public ?Seminar $seminar = null) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $copy = Seminar::seminarEmailCopyFor($this->seminar?->training_group);

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject($copy['theory_exam_subject'])
            ->view($copy['theory_exam_view'], [
                'recipient' => $notifiable,
                'seminar' => $this->seminar,
            ]);
    }
}
