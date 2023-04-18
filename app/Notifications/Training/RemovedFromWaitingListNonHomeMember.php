<?php

namespace App\Notifications\Training;

use App\Notifications\DiscordNotification;
use App\Notifications\DiscordNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class RemovedFromWaitingListNonHomeMember extends Notification implements ShouldQueue, DiscordNotification
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
                    ->view('emails.training.waiting_list_non_home_removal', ['recipient' => $notifiable]);
    }

    public function toDiscord(object $notifiable)
    {
        return [
            'content' => '',
            'embeds' => [
                [
                    'title' => 'User Removed from Waiting List',
                    'description' => 'A user has been removed from a waiting list(s) due to not being a home member.',
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
                            'value' => $this->waitingLists->pluck("name")->implode(", "),
                            'inline' => false,
                        ]
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
