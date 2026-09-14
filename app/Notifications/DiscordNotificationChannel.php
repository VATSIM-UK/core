<?php

namespace App\Notifications;

use App\Libraries\Discord;
use Illuminate\Support\Facades\Log;

class DiscordNotificationChannel
{
    public function send($notifiable, DiscordNotification $notification)
    {
        $messageContents = $notification->toDiscord($notifiable);

        $discordClient = new Discord;

        Log::info('Sending Discord notification', ['channel_id' => $notification->getChannel()]);
        $discordClient->sendMessageToChannel($notification->getChannel(), $messageContents);
    }
}
