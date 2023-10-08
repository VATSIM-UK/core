<?php

namespace App\Notifications\Training;

use App\Notifications\DiscordNotification;
use App\Notifications\DiscordNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class RemovedFromWaitingListInactiveAccount extends Notification implements DiscordNotification, ShouldQueue
{
    use Queueable;

    private $waitingLists;

    public function __construct(Collection $waitingLists)
    {
        $this->waitingLists = $waitingLists;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [DiscordNotificationChannel::class];
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
            ->view('emails.training.waiting_list_inactive_account', ['recipient' => $notifiable]);
    }

    public function toDiscord(object $notifiable)
    {
        return [
            'content' => '',
            'embeds' => [
                [
                    'title' => 'Member Removed from Waiting List',
                    'description' => 'A member has been removed from a waiting list(s) due to having an inactive network account i.e. NOT ATC hours.',
                    'fields' => [
                        [
                            'name' => 'CID',
                            'value' => $notifiable->id,
                            'inline' => true,
                        ],
                        [
                            'name' => 'Name',
                            'value' => $notifiable->name,
                            'inline' => true,
                        ],
                        [
                            'name' => 'Waiting List(s)',
                            'value' => $this->waitingLists->pluck('name')->implode(', '),
                            'inline' => false,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getChannel(): string
    {
        return config('services.discord.training_alerts_channel_id');
    }
}
