<?php

namespace App\Filament\Training\Resources\Seminars\Pages;

use App\Filament\Training\Resources\Seminars\SeminarResource;
use App\Filament\Training\Resources\Seminars\Widgets\SeminarStatsOverview;
use App\Services\Training\SeminarInvitationService;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewSeminar extends ViewRecord
{
    protected static string $resource = SeminarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('autoInvite')
                ->label('Enable Automatic Invitations')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Enable Automatic Invitations?')
                ->modalDescription(
                    'Automatic invitations will be enabled for this seminar. '.
                    'The system will immediately send invitations to eligible students until all available seminar places have been filled. '.
                    'Additional invitations will also be sent automatically if places become available.'
                )
                ->modalSubmitActionLabel('Enable')
                ->action(function (): void {
                    $this->record->update(['automatic_invitations_enabled' => true]);
                    app(SeminarInvitationService::class)->topUpAutomaticInvitations($this->record);
                })
                ->visible(fn () => ! $this->record->isSendingCutoffReached() && ! $this->record->automatic_invitations_enabled && auth()->user()->can('training.seminars.manage.*')),

            Action::make('disableAutoInvite')
                ->label('Disable Automatic Invitations')
                ->icon('heroicon-o-pause')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Disable Automatic Invitations?')
                ->modalDescription(
                    'No further invitations will be sent automatically for this seminar. '.
                    'Students who have already been invited or booked will not be affected. '.
                    'You can re-enable automatic invitations at any time.'
                )
                ->modalSubmitActionLabel('Disable')
                ->action(function (): void {
                    $this->record->update(['automatic_invitations_enabled' => false]);
                })
                ->visible(fn () => $this->record->automatic_invitations_enabled && auth()->user()->can('training.seminars.manage.*')),

            Action::make('manualClose')
                ->label(fn () => $this->record->closed_at ? 'Closed' : 'Close Seminar')
                ->icon('heroicon-o-lock-closed')
                ->color(fn () => $this->record->closed_at ? 'gray' : 'danger')
                ->disabled(fn () => (bool) $this->record->closed_at)
                ->requiresConfirmation()
                ->modalHeading('Close Seminar?')
                ->modalDescription(
                    'This will permanently close the seminar. '.
                    'No further invitations can be sent or responded to. '
                )
                ->modalSubmitActionLabel('Close Seminar')
                ->action(function (): void {
                    $this->record->update([
                        'automatic_invitations_enabled' => false,
                        'closed_at' => now(),
                    ]);
                })
                ->visible(fn () => auth()->user()->can('training.seminars.manage.*')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SeminarStatsOverview::class,
        ];
    }
}
