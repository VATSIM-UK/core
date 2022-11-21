<?php

namespace App\Notifications\Training;

use App\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class WaitingListRemovalAdded extends Notification implements ShouldQueue
{
    use Queueable;

    private $list_name;
    private $removal_date;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(String $list_name, Carbon $removal_date)
    {
        parent::__construct();

        $this->list_name = $list_name;
        $this->removal_date = $removal_date;
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
        $remainingDays  = Carbon::parse(Carbon::now())->diffInDays($this->removal_date);
        $subject        = 'Waiting List Activity Warning Notification';
        return (new MailMessage)
            ->from('atc-team@vatsim.uk', 'VATSIM UK - ATC Training')
            ->subject($subject)
            ->view('emails.training.waiting_list_removal_added', ['removal_date' => $this->removal_date, 'remaining_days' => $remainingDays, 'list_name' => $this->list_name, 'recipient' => $notifiable, 'subject' => $subject]);
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