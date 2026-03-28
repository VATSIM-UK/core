<?php

namespace App\Services\Training;

use App\Libraries\Discord;
use App\Models\Atc\PositionGroup;
use App\Models\Cts\ExamBooking;
use App\Models\Mship\Account;
use Carbon\CarbonImmutable;

class TrainingSuccessesAnnouncementService
{
    public function __construct(private readonly Discord $discord) {}

    public function announceExamPassed(ExamBooking $examBooking): void
    {
        $message = $this->buildExamPassedMessage($examBooking);
        $threadName = $examBooking->studentAccount()?->name.' - '.$examBooking->position_1;

        $this->postAnnouncement($message, $threadName);
    }

    public function announceTierEndorsement(Account $account, PositionGroup $positionGroup): void
    {
        $message = $this->buildTierEndorsementMessage($account, $positionGroup);
        $threadName = $account->name.' - '.$positionGroup->name;

        $this->postAnnouncement($message, $threadName);
    }

    public function buildExamPassedMessage(ExamBooking $examBooking): string
    {
        $startUtc = CarbonImmutable::parse($examBooking->start_date)->utc();
        $unix = $startUtc->getTimestamp();

        $emoji = config('training.discord.vatuk_emoji_name_and_id');
        $mention = $this->resolveMention($examBooking->studentAccount());

        $rating = match ($examBooking->exam) {
            'OBS' => 'S1',
            'TWR' => 'S2',
            'APP' => 'S3',
            'CTR' => 'C1',
            default => $examBooking->exam,
        };

        return "<:{$emoji}> Please join us in congratulating **{$mention}** on passing their **{$rating}** exam (<t:{$unix}:R>)!";
    }

    public function buildTierEndorsementMessage(Account $account, PositionGroup $positionGroup): string
    {
        $emoji = config('training.discord.vatuk_emoji_name_and_id');
        $mention = $this->resolveMention($account);

        return "<:{$emoji}> Please join us in congratulating **{$mention}** on achieving their **{$positionGroup->name}** endorsement!";
    }

    private function postAnnouncement(string $message, string $threadName): void
    {
        $channelId = config('training.discord.exam_success_channel_id');

        $response = $this->discord->sendMessageToChannel($channelId, [
            'content' => $message,
        ]);

        if (is_array($response) && ! empty($response['id'])) {
            $this->discord->createThreadFromMessage($channelId, $response['id'], data: [
                'name' => $threadName,
                'auto_archive_duration' => 4320, // 72 hours
            ]);
        }
    }

    private function resolveMention(?Account $account): string
    {
        return filled($account?->discord_id) ? "<@{$account->discord_id}>" : ($account?->name ?? 'Unknown');
    }
}
