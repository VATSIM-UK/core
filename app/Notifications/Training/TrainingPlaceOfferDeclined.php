<?php

declare(strict_types=1);

namespace App\Notifications\Training;

use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Notifications\DiscordNotification;
use App\Notifications\DiscordNotificationChannel;
use App\Notifications\Notification;

class TrainingPlaceOfferDeclined extends Notification implements DiscordNotification
{
    public function __construct(public TrainingPlaceOffer $trainingPlaceOffer) {}

    public function via($notifiable): array
    {
        $channels = [];

        if (! empty($this->getChannel())) {
            $channels[] = DiscordNotificationChannel::class;
        }

        return $channels;
    }

    public function toDiscord($notifiable)
    {
        $position = $this->trainingPlaceOffer->trainingPosition->position;

        return [
            'content' => null,
            'embeds' => [
                [
                    'title' => 'Training Place Offer Declined',
                    'description' => "**{$notifiable->name} ({$notifiable->id})** has declined the training place offer for **{$position->name} ({$position->callsign})**.",
                    'color' => 15158332,
                    'timestamp' => now()->toIso8601String(),
                ],
            ],
        ];
    }

    public function getChannel(): string
    {
        return $this->trainingPlaceOffer->trainingPosition?->training_team_discord_channel_id ?? '';
    }
}
