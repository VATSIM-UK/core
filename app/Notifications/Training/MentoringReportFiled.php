<?php

declare(strict_types=1);

namespace App\Notifications\Training;

use App\Filament\Training\Pages\Mentor\ViewMentoringReport;
use App\Models\Cts\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentoringReportFiled extends Notification implements ShouldQueue
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
        $subject = 'Session report finished';

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject($subject)
            ->view('emails.training.mentoring_report_filed', [
                'subject' => $subject,
                'recipient' => $notifiable,
                'session' => $this->session,
                'reportUrl' => ViewMentoringReport::getUrl(['sessionId' => $this->session->id]),
            ]);
    }
}
