<?php

namespace App\Services\Training;

use App\Libraries\Discord;
use App\Models\Cts\Session;
use Carbon\CarbonImmutable;

class MentoringAnnouncementService
{
    public function __construct(
        private readonly Discord $discord,
        private readonly MentorPermissionService $mentorPermissionService,
    ) {}

    public function canPostAnnouncement(Session $session, int $memberId): bool
    {
        if (filled($session->filed)) {
            return false;
        }

        if (filled($session->cancelled_datetime)) {
            return false;
        }

        if (CarbonImmutable::parse("{$session->taken_date} {$session->taken_from}")->isPast()) {
            return false;
        }

        return $session->mentor_id === $memberId;
    }

    public function postAnnouncement(Session $session, array $data): void
    {
        $channelId = config('training.discord.mentoring_announce_channel_id');

        $this->discord->sendMessageToChannel($channelId, [
            'content' => $this->buildMessage($session, $data),
            'allowed_mentions' => [
                'parse' => [],
                'roles' => $this->mentionedRoleIds($data),
            ],
        ]);
    }

    public function buildMessage(Session $session, array $data): string
    {
        $category = $this->mentorPermissionService->resolveCategoryForCtsCallsign($session->position);

        if ($category !== null && in_array($category, MentorPermissionService::pilotCategories(), true)) {
            return $this->buildPilotMessage($session, $data);
        }

        return $this->buildAtcMessage($session, $data);
    }

    public function buildAtcMessage(Session $session, array $data): string
    {
        [$unix, $mentions, $notesBlock] = $this->buildSharedParts($session, $data);

        $position = $data['position'] ?? $session->position;

        return ($mentions !== '' ? $mentions."\n" : '')
            ."**Upcoming ATC Mentoring Session**\n"
            ."There will be a mentoring session on **{$position}** on **<t:{$unix}:F>** (<t:{$unix}:R>)"
            .$notesBlock;
    }

    public function buildPilotMessage(Session $session, array $data): string
    {
        [$unix, $mentions, $notesBlock] = $this->buildSharedParts($session, $data);

        return ($mentions !== '' ? $mentions."\n" : '')
            ."**Upcoming Pilot Mentoring Session**\n"
            ."There will be a **{$session->position}** mentoring session on **<t:{$unix}:F>** (<t:{$unix}:R>)"
            .$notesBlock;
    }

    private function buildSharedParts(Session $session, array $data): array
    {
        $unix = CarbonImmutable::parse("{$session->taken_date} {$session->taken_from}")
            ->utc()
            ->getTimestamp();

        $mentions = $this->buildMentions($data);

        $notes = trim($data['notes'] ?? '');
        $notesBlock = $notes !== '' ? "\n\n**Notes:**\n{$notes}" : '';

        return [$unix, $mentions, $notesBlock];
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
            ! empty($data['ping_pilot']) ? config('training.discord.mentoring_pilot_role_id') : null,
            ! empty($data['ping_controller']) ? config('training.discord.mentoring_controller_role_id') : null,
        ]));
    }
}
