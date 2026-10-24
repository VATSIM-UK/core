<?php

namespace App\Notifications\Training\Mentoring;

use App\Enums\EmailType;
use App\Models\Cts\Session;
use App\Notifications\Contracts\HasEmailType;
use App\Services\IcsService;
use App\Support\Calendar\CalendarSequence;
use App\Support\Calendar\CalendarUid;
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

        $sessionDate = Carbon::parse($session->taken_date)->format('Y-m-d');
        $icsContent = IcsService::generate(
            uid: CalendarUid::forSession((int) $session->id),
            summary: "Mentoring Session - {$session->position}",
            description: "Student: {$studentName} ({$studentCid})\nPosition: {$session->position}\nRescheduled from {$this->previousDateTime}.",
            start: Carbon::parse("{$sessionDate} {$session->taken_from}"),
            end: Carbon::parse("{$sessionDate} {$session->taken_to}"),
            location: $session->position,
            sequence: CalendarSequence::forSession($session),
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
