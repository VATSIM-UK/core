<?php

namespace App\Notifications\Training\Mentoring;

use App\Enums\EmailType;
use App\Models\Cts\Session;
use App\Notifications\Contracts\HasEmailType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentoringSessionRescheduledStudentNotification extends Notification implements HasEmailType
{
    use Queueable;

    public function __construct(
        private Session $session,
        private string $previousDateTime,
    ) {}

    public function getEmailType(): EmailType
    {
        return EmailType::SessionRescheduledByMentor;
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
        $session = $this->session->loadMissing(['student', 'mentor']);

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('VATSIM UK - Mentoring Session Rescheduled')
            ->view('emails.training.mentoring.session_rescheduled_student', [
                'recipient' => $notifiable,
                'session' => $session,
                'position' => $session->position,
                'previousDateTime' => $this->previousDateTime,
                'sessionDateTime' => $session->formattedSessionDateTime(),
                'mentorName' => $session->mentor?->account?->name ?? 'TBD',
            ]);
    }
}
