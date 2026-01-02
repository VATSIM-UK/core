<?php

namespace App\Livewire\Training;

use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Services\Training\EndorsementService;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Livewire\Component;

class TrainingPlaceSoloEndorsement extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public TrainingPlace $trainingPlace;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Solo endorsements')
            ->description('Both solo endorsements related to the training place and other solo endorsements related to the same rating are displayed.')
            ->queryStringIdentifier('solo-endorsements')
            ->query(EndorsementService::getAllSoloEndorsementsIncludingRelatedPositionsForTrainingPlace($this->trainingPlace))
            ->defaultSort('created_at', 'desc')
            ->groups([
                Group::make('endorsement_category')
                    ->label('Category')
                    ->collapsible(),
            ])
            ->defaultGroup('endorsement_category')
            ->groupingSettingsHidden()
            ->columns([
                TextColumn::make('endorsable.description')->label('Position'),
                TextColumn::make('created_at')->label('Granted')->date('d/m/Y H:i'),
                TextColumn::make('expires_at')->label('Expires')->date('d/m/Y H:i'),
                TextColumn::make('duration')
                    ->label('Duration')
                    ->getStateUsing(fn ($record) => floor($record->created_at->diffInDays($record->expires_at)).' days')
                    ->summarize(
                        Sum::make()
                            ->label('Total')
                            ->formatStateUsing(fn ($state) => number_format($state, 0).' days')
                            ->using(fn ($query) => $query->get()->sum(fn ($record) => floor($record->created_at->diffInDays($record->expires_at))))
                    ),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->expires_at->isPast() ? 'Expired' : 'Active')
                    ->color(
                        fn (string $state): string => match ($state) {
                            'Expired' => 'danger',
                            'Active' => 'success',
                            default => 'primary',
                        }
                    ),
            ])
            ->headerActions([
                Action::make('issue_solo_endorsement')
                    ->label('Issue Solo Endorsement')
                    ->color('primary')
                    ->visible(fn () => auth()->check() && auth()->user()->can('endorsement.create.temporary'))
                    ->disabled(fn () => EndorsementService::hasActiveSoloEndorsement(
                        $this->trainingPlace->trainingPosition->position,
                        $this->trainingPlace->waitingListAccount->account
                    ))
                    ->form([
                        TextInput::make('days')
                            ->label('Duration (Days)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(90)
                            ->default(7)
                            ->helperText('Number of days the solo endorsement will be valid for'),
                    ])
                    ->action(function (array $data) {
                        $position = $this->trainingPlace->trainingPosition->position;
                        $account = $this->trainingPlace->waitingListAccount->account;
                        $creator = auth()->user();

                        EndorsementService::createTemporary(
                            $position,
                            $account,
                            $creator,
                            (int) $data['days']
                        );

                        Notification::make()
                            ->title('Solo endorsement issued successfully')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Issue Solo Endorsement')
                    ->modalDescription(fn () => 'Issue a solo endorsement for '.$this->trainingPlace->trainingPosition->position->name.' to '.$this->trainingPlace->waitingListAccount->account->name)
                    ->modalSubmitActionLabel('Issue Endorsement'),
            ])
            ->emptyStateHeading('No solo endorsements found');
    }

    public function render()
    {
        return view('livewire.training.training-place-solo-endorsement');
    }
}
