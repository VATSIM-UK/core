<?php

namespace App\Notifications\Training\Mentoring;

use App\Enums\EmailType;
use App\Models\Cts\Session;
use App\Notifications\Contracts\HasEmailType;
use App\Services\IcsService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentoringSessionRescheduledMentorNotification extends Notification implements HasEmailType
{
    use Queueable;

    public function __construct(
        private Session $session,
        private string $previousDateTime,
    ) {}

    public function getEmailType(): EmailType
    {
        return EmailType::MentorSessionRescheduled;
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
        $studentName = $session->student?->account?->name ?? 'Unknown';
        $studentCid = $session->student?->cid ?? 'Unknown';

        $icsContent = IcsService::generate(
            uid: "session-{$session->id}@vatsim.uk",
            summary: "Mentoring Session - {$session->position}",
            description: "Student: {$studentName} ({$studentCid})\nPosition: {$session->position}\n\nThis session has been rescheduled from its original time.",
            start: Carbon::parse("{$session->taken_date} {$session->taken_from}"),
            end: Carbon::parse("{$session->taken_date} {$session->taken_to}"),
            location: $session->position,
        );

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('VATSIM UK - Mentoring Session Rescheduled')
            ->view('emails.training.mentoring.session_rescheduled_mentor', [
                'recipient' => $notifiable,
                'session' => $session,
                'position' => $session->position,
                'previousDateTime' => $this->previousDateTime,
                'sessionDateTime' => $session->formattedSessionDateTime(),
                'studentName' => $studentName,
                'studentCid' => $studentCid,
            ])
            ->attachData($icsContent, 'event.ics', ['mime' => 'text/calendar']);
    }
}
