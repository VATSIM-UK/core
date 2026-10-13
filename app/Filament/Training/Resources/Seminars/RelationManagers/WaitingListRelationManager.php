<?php

namespace App\Filament\Training\Resources\Seminars\RelationManagers;

use App\Filament\Admin\Forms\Components\AccountSelect;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Services\Training\SeminarInvitationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WaitingListRelationManager extends RelationManager
{
    protected static string $relationship = 'waitingListAccounts';

    protected static ?string $title = 'Waiting List';

    private ?Collection $invitationsByAccountId = null;

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['account', 'waitingList', 'theoryReminder']))
            ->defaultSort('created_at', 'asc')
            ->columns([
                TextColumn::make('account_id')->label('CID'),
                TextColumn::make('account.name')->label('Name')->searchable(['name_first', 'name_last']),
                IconColumn::make('theory_exam_passed')->label('Theory Passed')->boolean(),
                TextColumn::make('created_at')->label('Joined')->dateTime('d/m/Y H:i'),
                TextColumn::make('invitation_status')
                    ->label('Invitation')
                    ->badge()
                    ->state(function (WaitingListAccount $record) {
                        if ($invitation = $this->invitationFor($record->account_id)) {
                            return $invitation->status->label();
                        }

                        return $this->isRemindedForSeminar($record) ? 'Theory Reminder' : 'Not Invited';
                    })
                    ->color(function (WaitingListAccount $record) {
                        if ($invitation = $this->invitationFor($record->account_id)) {
                            return $invitation->status->color();
                        }

                        return $this->isRemindedForSeminar($record) ? 'warning' : 'gray';
                    }),
            ])
            ->headerActions([
                Action::make('inviteNonMember')
                    ->label('Invite Member')
                    ->icon('heroicon-o-user-plus')
                    ->modalHeading('Invite Member to Seminar')
                    ->modalDescription('Invite a member who is not on the waiting list.')
                    ->form([
                        AccountSelect::make('account')->required(),
                    ])
                    ->action(function (array $data): void {
                        $account = $data['account_id'];

                        if ($this->isAlreadyInvited($account)) {
                            Notification::make()
                                ->title('Already invited')
                                ->danger()
                                ->send();

                            return;
                        }

                        $waitingListAccount = $this->waitingListAccountFor($account);

                        if ($waitingListAccount?->wasRemindedForSeminar($this->ownerRecord->id)) {
                            Notification::make()
                                ->title('Theory exam reminder sent')
                                ->body('This member was sent a theory exam reminder for this seminar, so they cannot be invited to it.')
                                ->danger()
                                ->send();

                            return;
                        }

                        app(SeminarInvitationService::class)->createInvitation(
                            $this->ownerRecord,
                            Account::query()->findOrFail($account),
                        );

                        Notification::make()
                            ->title('Invitation sent')
                            ->success()
                            ->send();

                        $this->refreshInvitations();
                    })
                    ->visible(fn () => $this->ownerRecord->canInvite() && auth()->user()->can('training.seminars.manage.*')),
            ])
            ->recordActions([
                Action::make('manualInvite')
                    ->label(fn (WaitingListAccount $record) => match (true) {
                        $this->isAlreadyInvited($record->account_id) => 'Already Invited',
                        $this->isRemindedForSeminar($record) => 'Theory Reminder',
                        ! $this->ownerRecord->canInvite() => 'At Capacity',
                        default => 'Invite',
                    })
                    ->icon(fn (WaitingListAccount $record) => $this->isRemindedForSeminar($record)
                        ? 'heroicon-o-exclamation-triangle'
                        : 'heroicon-o-paper-airplane')
                    ->color(fn (WaitingListAccount $record) => match (true) {
                        $this->isAlreadyInvited($record->account_id) => 'gray',
                        $this->isRemindedForSeminar($record) => 'warning',
                        ! $this->ownerRecord->canInvite() => 'gray',
                        default => 'primary',
                    })
                    ->disabled(fn (WaitingListAccount $record) => $this->isAlreadyInvited($record->account_id) || $this->isRemindedForSeminar($record) || ! $this->ownerRecord->canInvite())
                    ->action(function (WaitingListAccount $record): void {
                        app(SeminarInvitationService::class)->createInvitation(
                            $this->ownerRecord,
                            $record->account,
                            $record->id
                        );

                        $this->refreshInvitations();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Send Seminar Invitation')
                    ->modalDescription(fn ($record) => "Are you sure you want to manually invite {$record->account->name} to this seminar?")
                    ->modalIcon('heroicon-o-envelope')
                    ->modalIconColor('primary')
                    ->modalSubmitActionLabel('Send Invitation')
                    ->visible(fn () => auth()->user()->can('training.seminars.manage.*')),
            ]);
    }

    private function isAlreadyInvited(int $accountId): bool
    {
        return $this->invitationFor($accountId) !== null;
    }

    private function isRemindedForSeminar(WaitingListAccount $record): bool
    {
        return $record->wasRemindedForSeminar($this->ownerRecord->id);
    }

    private function waitingListAccountFor(int $accountId): ?WaitingListAccount
    {
        return $this->ownerRecord->waitingListAccounts()
            ->where('account_id', $accountId)
            ->first();
    }

    private function invitationFor(int $accountId)
    {
        return $this->loadInvitations()->get($accountId);
    }

    private function loadInvitations(): Collection
    {
        return $this->invitationsByAccountId ??= $this->ownerRecord->invitations()
            ->get()
            ->keyBy('account_id');
    }

    private function refreshInvitations(): void
    {
        $this->invitationsByAccountId = null;
        $this->ownerRecord->refresh();
    }
}
