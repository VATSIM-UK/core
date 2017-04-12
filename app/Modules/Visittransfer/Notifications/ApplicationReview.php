<?php

namespace App\Modules\Visittransfer\Notifications;

use Illuminate\Bus\Queueable;
use App\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Models\Application;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicationReview extends Notification implements ShouldQueue
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
        $subject = "[{$this->application->public_id}] New {$this->application->type_status} Application";

        return (new MailMessage)
            ->from('community@vatsim-uk.co.uk', 'VATSIM UK - Community Department')
            ->subject($subject)
            ->view('visittransfer::emails.community.new_application', [
                'application' => $this->application,
                'recipient' => $notifiable,
                'subject' => $subject,
            ]);
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
