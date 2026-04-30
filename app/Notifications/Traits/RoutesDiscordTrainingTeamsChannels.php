<?php

declare(strict_types=1);

namespace App\Notifications\Traits;

trait RoutesDiscordTrainingTeamsChannels
{
    /**
     * Determine the Discord channel ID based on the training position name.
     */
    protected function getDiscordChannelForCategory($category)
    {
        return match ($category) {
            'OBS to S1 Training' => config('services.discord.tgnc_team_channel_id'),
            'S2 Training' => config('services.discord.twr_team_channel_id'),
            'S3 Training' => config('services.discord.app_team_channel_id'),
            'C1 Training' => config('services.discord.enroute_team_channel_id'),
            'Heathrow GMC', 'Heathrow AIR', 'Heathrow APC' => config('services.discord.heathrow_team_channel_id'),

            default => null,
        };
    }
}
