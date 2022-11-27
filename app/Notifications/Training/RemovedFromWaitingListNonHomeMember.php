<?php

namespace App\Notifications\Training;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RemovedFromWaitingListNonHomeMember extends Notification
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
                    ->subject('UK Training Waiting List Removal')
                    ->view('emails.training.waiting_list_non_home_removal', ['recipient' => $notifiable]);
    }
}
