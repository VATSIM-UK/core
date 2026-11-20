<?php

namespace App\Filament\Training\Resources\Seminars\RelationManagers;

use App\Filament\Training\Pages\TrainingPlace\ViewTrainingPlace;
use App\Models\Mship\Qualification;
use App\Models\Training\Seminar\SeminarAttendee;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList\WaitingListAccount; // Imported TrainingPlace Model
use App\Services\Training\TrainingPlaceService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
                    ->label(fn (SeminarAttendee $record) => $this->hasTrainingPlace($record) ? 'Training Place Already Exists' : 'Create Training Place')
                    ->icon('heroicon-o-academic-cap')
                    ->modalDescription('Manually create a training place for this student. This does not send an offer email.')
                    ->color(fn (SeminarAttendee $record) => $this->hasTrainingPlace($record) ? 'gray' : 'success')
                    ->disabled(fn (SeminarAttendee $record) => $this->hasTrainingPlace($record))
                    ->visible(fn () => auth()->user()->can('update', $this->ownerRecord)
                        && ($this->ownerRecord->relationLoaded('waitingList')
                            ? $this->ownerRecord->waitingList !== null
                            : $this->ownerRecord->waitingList()->exists()))
                    ->schema([
                        Select::make('trainable')
                            ->label('Training Position / Qualification')
                            ->options(fn () => $this->trainableOptions())
                            ->required()
                            ->helperText('Select the training position or qualification to assign to this member.'),
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

                        $trainable = $this->resolveTrainable($data['trainable']);

                        if (! $trainable) {
                            Notification::make()
                                ->title('Cannot create training place')
                                ->body('The selected training position or qualification is not linked to this waiting list.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $trainingPlace = app(TrainingPlaceService::class)
                            ->createManualTrainingPlace($waitingListAccount, $trainable);

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

    protected function hasTrainingPlace(SeminarAttendee $record): bool
    {
        $trainables = $this->trainables();

        if ($trainables->isEmpty()) {
            return false;
        }

        $existingPlaces = TrainingPlace::query()
            ->where('account_id', $record->account_id)
            ->get(['trainable_type', 'trainable_id']);

        return $trainables->contains(fn (TrainingPosition|Qualification $trainable) => $existingPlaces->contains(
            fn (TrainingPlace $place) => $place->trainable_type === $trainable->getMorphClass()
                && (int) $place->trainable_id === (int) $trainable->getKey()
        ));
    }

    private function trainables(): Collection
    {
        return $this->ownerRecord
            ->loadMissing('waitingList.trainingPositions.position', 'waitingList.qualifications')
            ->waitingList?->trainables ?? collect();
    }

    private function trainableOptions(): array
    {
        return $this->trainables()
            ->mapWithKeys(fn (TrainingPosition|Qualification $trainable) => [
                $this->trainableKey($trainable) => $this->trainableLabel($trainable),
            ])
            ->all();
    }

    private function resolveTrainable(?string $key): TrainingPosition|Qualification|null
    {
        return $this->trainables()
            ->first(fn (TrainingPosition|Qualification $trainable) => $this->trainableKey($trainable) === $key);
    }

    private function trainableKey(TrainingPosition|Qualification $trainable): string
    {
        return $trainable->getMorphClass().':'.$trainable->getKey();
    }

    private function trainableLabel(TrainingPosition|Qualification $trainable): string
    {
        if ($trainable instanceof Qualification) {
            return "{$trainable->name_long} ({$trainable->code})";
        }

        return $trainable->position?->callsign ?? "Position #{$trainable->getKey()}";
    }
}
