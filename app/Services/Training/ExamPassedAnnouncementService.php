<?php

namespace App\Services\Training;

use App\Libraries\Discord;
use App\Models\Cts\ExamBooking;
use Carbon\CarbonImmutable;

class ExamPassedAnnouncementService
{
    public function __construct(private readonly Discord $discord) {}

    public function postAnnouncement(ExamBooking $examBooking, array $data): void
    {
        $channelId = config('training.discord.exam_success_channel_id');

        $message = $this->buildMessage($examBooking, $data);

        $response = $this->discord->sendMessageToChannel($channelId, [
            'content' => $message,
        ]);

        if (is_array($response) && ! empty($response['id'])) {
            $threadName = ($examBooking->studentAccount()?->name.' - '.$examBooking->position_1);
            $this->discord->createThreadFromMessage($channelId, $response['id'], data: [
                'name' => $threadName,
                'auto_archive_duration' => 4320, // 72 hours
            ]);
        }
    }

    public function buildMessage(ExamBooking $examBooking, array $data): string
    {
        $startUtc = CarbonImmutable::parse($examBooking->start_date)->utc();
        $unix = $startUtc->getTimestamp();

        $emoji = config('training.discord.vatuk_emoji_name_and_id');
        $mention = filled($examBooking->studentAccount()?->discord_id) ? "<@{$examBooking->studentAccount()?->discord_id}>" : $examBooking->studentAccount()?->name;

        return "<:{$emoji}> Please join us in congratulating **{$mention}** on passing their **{$examBooking->exam}** exam (<t:{$unix}:R>)!";
    }
}
