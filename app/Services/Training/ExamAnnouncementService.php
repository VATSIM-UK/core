<?php

namespace App\Services\Training;

use App\Enums\PilotExamType;
use App\Libraries\Discord;
use App\Models\Cts\ExamBooking;
use Carbon\CarbonImmutable;

class ExamAnnouncementService
{
    public function __construct(private readonly Discord $discord) {}

    public function canPostAnnouncement(ExamBooking $examBooking, int $memberId): bool
    {
        if ($examBooking->finished == ExamBooking::FINISHED_FLAG) {
            return false;
        }

        $examiners = $examBooking->examiners;

        return $examiners->senior === $memberId || $examiners->other === $memberId || $examiners->trainee === $memberId;
    }

    public function postAnnouncement(ExamBooking $examBooking, array $data): void
    {
        $channelId = config('training.discord.exam_announce_channel_id');

        $message = $this->buildMessage($examBooking, $data);

        $this->discord->sendMessageToChannel($channelId, [
            'content' => $message,
            'allowed_mentions' => [
                'parse' => [],
                'roles' => $this->mentionedRoleIds($data),
            ],
        ]);
    }

    public function buildMessage(ExamBooking $examBooking, array $data): string
    {
        if ($examBooking->isPilotExam()) {
            return $this->buildPilotMessage($examBooking, $data);
        }

        return $this->buildAtcMessage($examBooking, $data);
    }

    public function buildAtcMessage(ExamBooking $examBooking, array $data)
    {
        $startUtc = CarbonImmutable::parse($examBooking->start_date)->utc();
        $unix = $startUtc->getTimestamp();

        $mentions = $this->buildMentions($data);

        $notes = trim($data['notes'] ?? '');
        $notesBlock = $notes !== '' ? "\n\n**Notes:**\n{$notes}" : '';

        return ($mentions !== '' ? $mentions."\n" : '')
            ."**Upcoming {$examBooking->exam} Exam**\n"
            ."There will be an exam on **{$examBooking->position_1}** on **<t:{$unix}:F>** (<t:{$unix}:R>)"
            .$notesBlock;
    }

    public function buildPilotMessage(ExamBooking $examBooking, array $data): string
    {
        $startUtc = CarbonImmutable::parse($examBooking->start_date)->utc();
        $unix = $startUtc->getTimestamp();

        $mentions = $this->buildMentions($data);

        $notes = trim($data['notes'] ?? '');
        $notesBlock = $notes !== '' ? "\n\n**Notes:**\n{$notes}" : '';

        $label = PilotExamType::labelFor($examBooking->exam);

        return ($mentions !== '' ? $mentions."\n" : '')
            ."**Upcoming {$label} Exam**\n"
            ."There will be a **{$label}** pilot exam on **<t:{$unix}:F>** (<t:{$unix}:R>)"
            .$notesBlock;
    }

    private function buildMentions(array $data): string
    {
        return collect($this->mentionedRoleIds($data))
            ->map(fn (string $roleId) => "<@&{$roleId}>")
            ->implode(' ');
    }

    /**
     * Role IDs the announcement is allowed to ping; user-supplied notes may not mention anyone.
     */
    private function mentionedRoleIds(array $data): array
    {
        return array_values(array_filter([
            ! empty($data['ping_exam_pilot']) ? config('training.discord.exam_pilot_role_id') : null,
            ! empty($data['ping_exam_controller']) ? config('training.discord.exam_controller_role_id') : null,
        ]));
    }
}
