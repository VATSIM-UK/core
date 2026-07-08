<?php

namespace App\Notifications\Training\Mentoring;

use App\Enums\EmailType;
use App\Models\Cts\Session;
use App\Notifications\Contracts\HasEmailType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentoringSessionCancelledMentorNotification extends Notification implements HasEmailType
{
    use Queueable;

    public function __construct(
        private Session $session,
        private string $reason,
    ) {}

    public function getEmailType(): EmailType
    {
        return EmailType::MentorSessionCancelled;
    }

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
            ->subject('VATSIM UK - Mentoring Session Cancelled')
            ->view('emails.training.mentoring.session_cancelled_mentor', [
                'recipient' => $notifiable,
                'session' => $this->session,
                'reason' => $this->reason,
            ]);
    }
}
