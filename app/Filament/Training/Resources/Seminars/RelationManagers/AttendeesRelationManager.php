<?php

namespace App\Filament\Training\Resources\Seminars\RelationManagers;

use App\Filament\Training\Pages\TrainingPlace\ViewTrainingPlace;
use App\Models\Training\Seminar\SeminarAttendee;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Services\Training\TrainingPlaceService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendeesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendees';

    protected static ?string $title = 'Attendees';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['account', 'invitation']))
            ->columns([
                TextColumn::make('account_id')->label('CID'),
                TextColumn::make('account.name')->label('Name')->searchable(['name_first', 'name_last']),
            ])
            ->recordActions([
                Action::make('createTrainingPlace')
                    ->label('Create Training Place')
                    ->icon('heroicon-o-academic-cap')
                    ->color('success')
                    ->visible(fn () => auth()->user()->can('training.seminars.manage.*')
                        && ($this->ownerRecord->relationLoaded('waitingList')
                            ? $this->ownerRecord->waitingList !== null
                            : $this->ownerRecord->waitingList()->exists()))
                    ->schema([
                        Select::make('training_position_id')
                            ->label('Training Position')
                            ->options(fn () => $this->ownerRecord
                                ->loadMissing('waitingList.trainingPositions.position')
                                ->waitingList?->trainingPositions
                                ->mapWithKeys(fn ($tp) => [$tp->id => $tp->position?->callsign ?? "Position #{$tp->id}"])
                                ->toArray() ?? [])
                            ->required()
                            ->helperText('Select the training position to assign to this member.'),
                    ])
                    ->action(function (SeminarAttendee $record, array $data) {
                        $waitingListAccount = WaitingListAccount::query()
                            ->where('list_id', $record->seminar->waiting_list_id)
                            ->where('account_id', $record->account_id)
                            ->first();

                        if (! $waitingListAccount) {
                            Notification::make()
                                ->title('Cannot create training place')
                                ->body('This member is not on the waiting list for this seminar.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $trainingPosition = TrainingPosition::query()->findOrFail($data['training_position_id']);
                        $service = app(TrainingPlaceService::class);
                        $trainingPlace = $service->createManualTrainingPlace($waitingListAccount, $trainingPosition);

                        Notification::make()
                            ->title('Training place created')
                            ->success()
                            ->actions([
                                Action::make('view')
                                    ->label('View Training Place')
                                    ->url(ViewTrainingPlace::getUrl(['trainingPlaceId' => $trainingPlace->id]))
                                    ->markAsRead(),
                            ])
                            ->send();
                    }),
            ]);
    }
}
