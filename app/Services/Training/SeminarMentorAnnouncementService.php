<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Libraries\Discord;
use App\Models\Training\Seminar\Seminar;
use Carbon\CarbonImmutable;

class SeminarMentorAnnouncementService
{
    public function __construct(private readonly Discord $discord) {}

    public function announceToMentors(Seminar $seminar): void
    {
        $this->discord->sendMessageToChannel(
            $seminar->mentorsDiscordChannelId(),
            ['embeds' => [$this->buildEmbed($seminar)]]
        );
    }

    public function buildEmbed(Seminar $seminar): array
    {
        $startUnix = CarbonImmutable::parse($seminar->startsAt())->utc()->getTimestamp();

        return [
            'title' => "{$seminar->name} has been scheduled",
            'description' => "The next seminar is scheduled for <t:{$startUnix}:F> (<t:{$startUnix}:R>).",
            'color' => 0x25ADE3,
        ];
    }
}
