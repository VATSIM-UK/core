<?php

namespace App\Modules\NetworkData\Notifications;

use Illuminate\Bus\Queueable;
use App\Modules\NetworkData\Models\Atc;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;

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

    public function toSlack()
    {
        return (new SlackMessage)
            ->success()
            ->content('Your recent controlling session on '.$this->atcSession->callsign.' was recorded! What is this?  Find our more on the forum! https://community.vatsim-uk.co.uk/news/community/home-is-where-the-heart-is-r39/')
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
