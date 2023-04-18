<?php

namespace App\Notifications;

interface DiscordNotification
{
    public function toDiscord(object $notifiable);

    public function getChannel(): string;
}
