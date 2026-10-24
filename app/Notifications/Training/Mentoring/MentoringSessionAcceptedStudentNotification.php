<?php

namespace App\Notifications\Training\Mentoring;

use App\Enums\CalendarViewer;
use App\Enums\EmailType;
use App\Models\Cts\Session;
use App\Notifications\Contracts\HasEmailType;
use App\Services\IcsService;
use App\Support\Calendar\CalendarEventFactory;
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

        $icsContent = IcsService::event(CalendarEventFactory::forSession($session, CalendarViewer::Student));

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
