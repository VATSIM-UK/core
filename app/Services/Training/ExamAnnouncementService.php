<?php

namespace App\Services\Training;

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

        dd($message);

        $this->discord->sendMessageToChannel($channelId, [
            'content' => $message,
        ]);
    }

    public function buildMessage(ExamBooking $examBooking, array $data): string
    {
        $startUtc = CarbonImmutable::parse($examBooking->start_date)->utc();
        $unix = $startUtc->getTimestamp();

        $mentions = $this->buildMentions($data);

        $notes = trim($data['notes'] ?? '');
        $notesBlock = $notes !== '' ? "\n\n**Notes:**\n{$notes}" : '';

        return ($mentions !== '' ? $mentions . "\n" : '')
            . "**Upcoming {$examBooking->exam} Exam**\n"
            . "There will be an exam on **{$examBooking->position_1}** on **<t:{$unix}:F>** (<t:{$unix}:R>)"
            . $notesBlock;
    }

    private function buildMentions(array $data): string
    {
        $pilotRoleId = config('training.discord.exam_pilot_role_id');
        $controllerRoleId = config('training.discord.exam_controller_role_id');

        $pingPilot = !empty($data['ping_exam_pilot']);
        $pingController = !empty($data['ping_exam_controller']);

        return collect([
            $pingPilot && filled($pilotRoleId) ? "<@&{$pilotRoleId}>" : null,
            $pingController && filled($controllerRoleId) ? "<@&{$controllerRoleId}>" : null,
        ])->filter()->implode(' ');
    }
}
