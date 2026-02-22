<?php

namespace App\Livewire\Training;

use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPlace\TrainingPlaceLeaveOfAbsence;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class LeaveOfAbsencesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public TrainingPlace $trainingPlace;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Leaves of Absence')
            ->queryStringIdentifier('leave-of-absences')
            ->query(TrainingPlaceLeaveOfAbsence::query()->where('training_place_id', $this->trainingPlace->id))
            ->defaultSort('begins_at', 'desc')
            ->columns([
                TextColumn::make('begins_at')
                    ->label('Start')
                    ->date('d/m/Y'),

                TextColumn::make('ends_at')
                    ->label('End')
                    ->date('d/m/Y'),

                TextColumn::make('duration')
                    ->label('Duration')
                    ->getStateUsing(fn (TrainingPlaceLeaveOfAbsence $record) => ceil($record->begins_at->diffInDays($record->ends_at)).' days'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->getStateUsing(fn (TrainingPlaceLeaveOfAbsence $record) => $record->isActive())
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(60)
                    ->tooltip(fn (TrainingPlaceLeaveOfAbsence $record) => $record->reason),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Create LOA')
                    ->icon('heroicon-o-plus')
                    ->model(TrainingPlaceLeaveOfAbsence::class)
                    ->modalHeading('Create a Leave of Absence')
                    ->modalDescription('Adding a leave of absence will stop automated checking for this training place during the selected period.')
                    ->modalSubmitActionLabel('Create LOA')
                    ->createAnother(false)
                    ->visible(fn () => auth()->user()->can('training-places.loas.create.*'))
                    ->form([
                        Grid::make(2)->schema([
                            DatePicker::make('begins_at')
                                ->label('Start Date')
                                ->required()
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->live()
                                ->minDate(Carbon::now()->startOfDay())
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                    $end = $get('ends_at');
                                    if ($end && Carbon::parse($end)->lt(Carbon::parse($state))) {
                                        $set('ends_at', null);
                                    }
                                }),

                            DatePicker::make('ends_at')
                                ->label('End Date')
                                ->required()
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->minDate(fn (Get $get) => $get('begins_at')),
                        ]),
                        Textarea::make('reason')
                            ->label('Reason')
                            ->placeholder('Please provide a reason for the leave of absence.')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['training_place_id'] = $this->trainingPlace->id;
                        $data['ends_at'] = Carbon::parse($data['ends_at'])->endOfDay();

                        return $data;
                    })
                    ->before(function (CreateAction $action, array $data) {
                        $this->abortIfOverlapping($action, $data);
                    }),
            ])
            ->actions([
                Action::make('end_loa_early')
                    ->label('End Early')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('End Leave of Absence Early')
                    ->modalDescription('This will end the leave of absence immediately. Automated availability checking will resume for this training place.')
                    ->modalSubmitActionLabel('End Early')
                    ->form([
                        Textarea::make('reason')
                            ->label('Reason')
                            ->placeholder('Please provide a reason for ending the leave of absence early.')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (TrainingPlaceLeaveOfAbsence $record) => $record->isActive() && auth()->user()->can('training-places.loas.end-early.*'))
                    ->action(function (TrainingPlaceLeaveOfAbsence $record, array $data) {
                        $record->update(['ends_at' => now()]);

                        $record->trainingPlace->waitingListAccount->account->addNote('training', "Leave of absence ended early. Reason: {$data['reason']}", auth()->id());

                        Notification::make()
                            ->title('Leave of absence ended early')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No leaves of absences');
    }

    private function abortIfOverlapping(CreateAction $action, array $data): void
    {
        $beginsAt = Carbon::parse($data['begins_at']);
        $endsAt = Carbon::parse($data['ends_at']);

        $query = TrainingPlaceLeaveOfAbsence::query()->where('training_place_id', $this->trainingPlace->id)->overlapping($beginsAt, $endsAt);

        if ($query->exists()) {
            Notification::make()
                ->title('Overlapping Leave of Absence')
                ->body('This training place already has a leave of absence during the selected period.')
                ->danger()
                ->send();

            $action->halt();
        }
    }

    public function render()
    {
        return view('livewire.training.leave-of-absences-table');
    }
}
