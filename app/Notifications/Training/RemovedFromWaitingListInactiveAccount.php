<?php

namespace App\Notifications\Training;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RemovedFromWaitingListInactiveAccount extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
        ->from('support@vatsim.uk', 'VATSIM UK - Member Services')
        ->subject('UK ATC training waiting list removal')
        ->view('emails.training.waiting_list_inactive_account', ['recipient' => $notifiable]);
    }
}
