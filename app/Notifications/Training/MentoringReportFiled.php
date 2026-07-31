<?php

declare(strict_types=1);

namespace App\Notifications\Training;

use App\Enums\EmailType;
use App\Filament\Training\Pages\Mentor\ViewMentoringReport;
use App\Models\Cts\Session;
use App\Notifications\Contracts\HasEmailType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentoringReportFiled extends Notification implements HasEmailType, ShouldQueue
{
    use Queueable;

    public function __construct(public Session $session) {}

    public function getEmailType(): EmailType
    {
        return EmailType::MentoringReportFiled;
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
        $subject = 'Session report finished';

        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject($subject)
            ->view('emails.training.mentoring_report_filed', [
                'subject' => $subject,
                'recipient' => $notifiable,
                'session' => $this->session,
                'reportUrl' => ViewMentoringReport::getUrl(['sessionId' => $this->session->id], panel: 'training'),
            ]);
    }
}
