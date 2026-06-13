<?php

namespace App\Notifications\Training\Mentoring;

use App\Models\Cts\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentoringReportOutstandingNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Session $session) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->from(config('mail.from.address'), 'VATSIM UK - Training Department')
            ->subject('TBC')
            ->view('emails.training.mentoring.outstanding_mentoring_report', [
                'recipient' => $notifiable,
                'session' => $this->session,
            ]);
    }
}
