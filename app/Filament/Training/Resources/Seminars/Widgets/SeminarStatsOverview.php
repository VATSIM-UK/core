<?php

namespace App\Filament\Training\Resources\Seminars\Widgets;

use App\Enums\SeminarInvitationStatus;
use App\Models\Training\Seminar\Seminar;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SeminarStatsOverview extends StatsOverviewWidget
{
    public ?Seminar $record = null;

    protected function getStats(): array
    {
        $seminar = $this->record;

        if (! $seminar) {
            return [];
        }

        $attendees = $seminar->attendees()->count();
        $invitationsSent = $seminar->invitations()->count();
        $pending = $seminar->invitations()->where('status', SeminarInvitationStatus::Sent->value)->count();
        $declined = $seminar->invitations()
            ->whereIn('status', [
                SeminarInvitationStatus::NotInterested->value,
                SeminarInvitationStatus::CannotAttend->value,
                SeminarInvitationStatus::RemovedNoResponse->value,
                SeminarInvitationStatus::RemovedTwoCannotAttend->value,
            ])->count();

        return [
            Stat::make('Total Invitations Sent', $invitationsSent)
                ->icon('heroicon-o-paper-airplane'),

            Stat::make('Confirmed Attendees', (string) $attendees)
                ->icon('heroicon-o-user-group')
                ->description("{$attendees} / {$seminar->capacity}"),

            Stat::make('Pending Responses', $pending)
                ->icon('heroicon-o-clock'),

            Stat::make('Declined', $declined)
                ->icon('heroicon-o-x-circle'),
        ];
    }
}
