<?php

namespace App\Notifications\Training\Mentoring;

use App\Models\Cts\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentoringSessionCancelledMentorNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Session $session,
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
        $this->session->loadMissing('student');

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('Mentoring Session Cancelled')
            ->view('emails.training.mentoring.session_cancelled_mentor', [
                'recipient' => $notifiable,
                'session' => $this->session,
                'reason' => $this->reason,
            ]);
    }
}
