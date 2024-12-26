<?php

namespace App\Notifications;

use App\Libraries\Discord;

class DiscordNotificationChannel
{
    public function send($notifiable, DiscordNotification $notification)
    {
        $messageContents = $notification->toDiscord($notifiable);

        $discordClient = new Discord;
        $discordClient->sendMessageToChannel($notification->getChannel(), $messageContents);
    }
}
