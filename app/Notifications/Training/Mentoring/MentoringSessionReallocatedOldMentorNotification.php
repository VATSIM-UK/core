<?php

namespace App\Notifications\Training\Mentoring;

use App\Enums\EmailType;
use App\Models\Cts\Session;
use App\Notifications\Contracts\HasEmailType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentoringSessionReallocatedOldMentorNotification extends Notification implements HasEmailType
{
    use Queueable;

    public function __construct(
        private Session $session,
        private string $reason,
        private string $newMentorName,
    ) {}

    public function getEmailType(): EmailType
    {
        return EmailType::MentorSessionReallocated;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $session = $this->session->loadMissing(['student', 'mentor']);

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('VATSIM UK - Mentoring Session Reallocated')
            ->view('emails.training.mentoring.session_reallocated_old_mentor', [
                'recipient' => $notifiable,
                'session' => $session,
                'position' => $session->position,
                'sessionDateTime' => $session->formattedSessionDateTime(),
                'studentName' => $session->student?->account?->name ?? 'Unknown',
                'newMentorName' => $this->newMentorName,
                'reason' => $this->reason,
            ]);
    }
}
