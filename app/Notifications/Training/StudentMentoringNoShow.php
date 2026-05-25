<?php

declare(strict_types=1);

namespace App\Notifications\Training;

use App\Models\Cts\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentMentoringNoShow extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Session $session) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = 'Student mentoring session no-show';

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject($subject)
            ->view('emails.training.student_mentoring_no_show', [
                'subject' => $subject,
                'recipient' => $notifiable,
                'session' => $this->session,
            ]);
    }
}
