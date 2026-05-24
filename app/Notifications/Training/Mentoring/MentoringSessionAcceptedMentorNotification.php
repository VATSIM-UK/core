<?php

namespace App\Notifications\Training\Mentoring;

use App\Models\Cts\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentoringSessionAcceptedMentorNotification extends Notification
{
    use Queueable;

    public function __construct(private Session $session) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $session = $this->session->loadMissing(['student', 'mentor']);

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('Mentoring Session Accepted')
            ->view('emails.training.mentoring.session_accepted_mentor', [
                'recipient' => $notifiable,
                'session' => $session,
                'position' => $session->position,
                'sessionDateTime' => $session->formattedSessionDateTime(),
                'studentName' => $session->student?->account?->name ?? 'Unknown',
                'studentCid' => $session->student?->cid ?? 'Unknown',
            ]);
    }
}
