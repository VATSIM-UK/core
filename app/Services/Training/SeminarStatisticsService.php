<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Enums\SeminarInvitationStatus;
use App\Models\Training\Seminar\Seminar;
use App\Models\Training\WaitingList\WaitingListTheoryReminder;
use Illuminate\Support\Collection;

class SeminarStatisticsService
{
    public function statisticsFor(Seminar $seminar): array
    {
        $invitationCounts = $seminar->invitations()
            ->toBase()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $expired = $this->countInvitations(
            $invitationCounts,
            SeminarInvitationStatus::RemovedNoResponse,
            SeminarInvitationStatus::ShortNoticeNoResponse,
        );

        $cannotAttend = $this->countInvitations(
            $invitationCounts,
            SeminarInvitationStatus::CannotAttend,
            SeminarInvitationStatus::RemovedTwoCannotAttend,
        );

        $notInterested = $this->countInvitations($invitationCounts, SeminarInvitationStatus::NotInterested);

        return [
            'invitations_sent' => (int) $invitationCounts->sum(),
            'confirmed_attendees' => $seminar->attendees()->count(),
            'pending_responses' => $this->countInvitations($invitationCounts, SeminarInvitationStatus::Sent),
            'declined_invitations' => $expired + $cannotAttend + $notInterested,
            'expired' => $expired,
            'cannot_attend' => $cannotAttend,
            'not_interested' => $notInterested,
            'removed_from_waiting_list' => $this->countInvitations(
                $invitationCounts,
                SeminarInvitationStatus::NotInterested,
                SeminarInvitationStatus::RemovedNoResponse,
                SeminarInvitationStatus::RemovedTwoCannotAttend,
            ),
            'theory_reminders' => WaitingListTheoryReminder::query()
                ->where('seminar_id', $seminar->id)
                ->count(),
        ];
    }

    private function countInvitations(Collection $invitationCounts, SeminarInvitationStatus ...$statuses): int
    {
        return collect($statuses)
            ->sum(fn (SeminarInvitationStatus $status): int => (int) ($invitationCounts[$status->value] ?? 0));
    }
}
