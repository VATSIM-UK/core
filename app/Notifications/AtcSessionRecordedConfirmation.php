<?php

namespace App\Notifications;

use App\Models\NetworkData\Atc;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use SlackUser;

class AtcSessionRecordedConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    private $atcSession = null;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Atc $atcSession)
    {
        parent::__construct();

        $this->atcSession = $atcSession;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->success()
            ->to('@'.SlackUser::method('info', ['user' => $notifiable->slack_id])->user->name)
            ->content('Your recent controlling session on '.$this->atcSession->callsign.' was recorded! What is this?  Find our more on the forum! https://community.vatsim.uk/news/community/home-is-where-the-heart-is-r39/')
            ->attachment(function ($attachment) {
                $attachment->title('Session '.$this->atcSession->public_id.' - '.$this->atcSession->callsign)
                           ->fields([
                               'Connected At' => $this->atcSession->connected_at->toDateTimeString(),
                               'Disconnected At' => $this->atcSession->disconnected_at->toDateTimeString(),
                               'Time Recorded (Mins)' => $this->atcSession->minutes_online,
                           ]);
            });
    }
}
