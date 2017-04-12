<?php

namespace App\Modules\Visittransfer\Notifications;

use Illuminate\Bus\Queueable;
use App\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Models\Application;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicationAccepted extends Notification implements ShouldQueue
{
    use Queueable;

    private $application;

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
            ->from('community@vatsim-uk.co.uk', 'VATSIM UK - Community Department')
            ->subject($subject)
            ->view('visittransfer::emails.training.accepted_application', ['recipient' => $this->application->account, 'subject' => $subject, 'application' => $this->application]);
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
