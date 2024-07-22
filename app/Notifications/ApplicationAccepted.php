<?php

namespace App\Notifications;

use App\Models\VisitTransferLegacy\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicationAccepted extends Notification implements ShouldQueue
{
    use Queueable;

    public $application;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Application $application)
    {
        parent::__construct();

        $this->application = $application;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = "[{$this->application->public_id}] New {$this->application->facility->name} {$this->application->type_string} Applicant";

        return (new MailMessage)
            ->from('community@vatsim.uk', 'VATSIM UK - Community Department')
            ->subject($subject)
            ->view('visit-transfer.emails.training.accepted_application', ['recipientName' => strtoupper($this->application->facility->training_team).' Training Team', 'subject' => $subject, 'application' => $this->application]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [];
    }
}
