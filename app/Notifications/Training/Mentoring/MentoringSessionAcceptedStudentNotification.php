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

class MentoringSessionAcceptedStudentNotification extends Notification implements HasEmailType
{
    use Queueable;

    public function __construct(private Session $session) {}

    public function getEmailType(): EmailType
    {
        return EmailType::SessionAcceptedByMentor;
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
        $mentorName = $session->mentor?->account?->name ?? 'TBD';

        $sessionDate = $session->taken_date->format('Y-m-d');
        $icsContent = IcsService::generate(
            uid: "session-{$session->id}@vatsim.uk",
            summary: "Mentoring Session - {$session->position}",
            description: "Position: {$session->position}\nMentor: {$mentorName}",
            start: Carbon::parse("{$sessionDate} {$session->taken_from}"),
            end: Carbon::parse("{$sessionDate} {$session->taken_to}"),
            location: $session->position,
        );

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('VATSIM UK - Mentoring Session Booked')
            ->view('emails.training.mentoring.session_accepted_student', [
                'recipient' => $notifiable,
                'session' => $session,
                'position' => $session->position,
                'sessionDateTime' => $session->formattedSessionDateTime(),
                'mentorName' => $mentorName,
            ])
            ->attachData($icsContent, 'event.ics', ['mime' => 'text/calendar']);
    }
}
