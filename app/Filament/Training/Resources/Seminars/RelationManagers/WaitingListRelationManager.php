<?php

namespace App\Filament\Training\Resources\Seminars\RelationManagers;

use App\Filament\Admin\Forms\Components\AccountSelect;
use App\Models\Mship\Account;
use App\Services\Training\SeminarInvitationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WaitingListRelationManager extends RelationManager
{
    protected static string $relationship = 'waitingListAccounts';

    protected static ?string $title = 'Waiting List';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['account', 'waitingList']))
            ->defaultSort('created_at', 'asc')
            ->columns([
                TextColumn::make('account_id')->label('CID'),
                TextColumn::make('account.name')->label('Name')->searchable(['name_first', 'name_last']),
                IconColumn::make('theory_exam_passed')->label('Theory Passed')->boolean(),
                TextColumn::make('created_at')->label('Joined')->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([
                Action::make('inviteNonMember')
                    ->label('Invite Member')
                    ->icon('heroicon-o-user-plus')
                    ->modalHeading('Invite Member to Seminar')
                    ->modalDescription('Invite a member who is not on the waiting list.')
                    ->form([
                        AccountSelect::make('account'),
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

                        app(SeminarInvitationService::class)->createInvitation(
                            $this->ownerRecord,
                            app(Account::class)->findOrFail($account),
                        );

                        Notification::make()
                            ->title('Invitation sent')
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => $this->ownerRecord->canInvite() && auth()->user()->can('training.seminars.manage.*')),
            ])
            ->recordActions([
                Action::make('manualInvite')
                    ->label(fn ($record) => match (true) {
                        $this->isAlreadyInvited($record->account_id) => 'Already Invited',
                        ! $this->ownerRecord->canInvite() => 'At Capacity',
                        default => 'Invite',
                    })
                    ->icon('heroicon-o-paper-airplane')
                    ->color(fn ($record) => match (true) {
                        $this->isAlreadyInvited($record->account_id) => 'gray',
                        ! $this->ownerRecord->canInvite() => 'gray',
                        default => 'primary',
                    })
                    ->disabled(fn ($record) => $this->isAlreadyInvited($record->account_id) || ! $this->ownerRecord->canInvite())
                    ->action(function ($record): void {
                        app(SeminarInvitationService::class)->createInvitation(
                            $this->ownerRecord,
                            $record->account,
                            $record->id
                        );
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
        return $this->ownerRecord->invitations()->where('account_id', $accountId)->exists();
    }
}
