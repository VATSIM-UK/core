<?php

namespace App\Notifications\Training;

use App\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class WaitingListAtcTopTen extends Notification implements ShouldQueue
{
    use Queueable;

    private $list_name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $list_name)
    {
        parent::__construct();

        $this->list_name = $list_name;
    }

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
            ->from('atc-team@vatsim.uk', 'VATSIM UK - ATC Training')
            ->subject('You are now top 10 in an ATC waiting list')
            ->view('emails.training.waiting_list_atc_top_ten', ['list_name' => $this->list_name, 'recipient' => $notifiable, 'subject' => 'You are now top 10 in an ATC waiting list']);
    }
}
