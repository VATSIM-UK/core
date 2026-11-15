<?php

namespace App\Livewire\Training;

use App\Libraries\UKCP;
use App\Models\Atc\Position;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Services\Training\EndorsementService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Livewire\Component;

class TrainingPlaceSoloEndorsement extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
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
                TextColumn::make('created_at')->label('Granted')->dateTime(),
                TextColumn::make('expires_at')->label('Expires')->dateTime(),
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
                    ->form([
                        Select::make('position_id')
                            ->label('Position')
                            ->options(fn () => $this->getEndorsablePositions()->pluck('callsign', 'id'))
                            ->default(fn () => $this->trainingPlace->trainingPosition->position_id)
                            ->searchable()
                            ->required()
                            ->live(),
                        TextInput::make('days')
                            ->label('Duration (Days)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(function (Get $get) {
                                if (auth()->user()?->can('endorsement.bypass.maximumdays')) {
                                    return null;
                                }

                                $position = Position::find($get('position_id'));

                                return $position
                                    ? EndorsementService::maximumTemporaryDays($position, $this->trainingPlace->account)
                                    : 0;
                            })
                            ->default(7)
                            ->helperText('Number of days the solo endorsement will be valid for. Solo endorsements are limited to 90 days in total per position.'),
                        Textarea::make('notes')
                            ->label('Notes / Restrictions')
                            ->helperText('Optional. This is saved as a training note on the account.'),
                    ])
                    ->action(function (array $data) {
                        $position = Position::findOrFail($data['position_id']);
                        $account = $this->trainingPlace->account;
                        $creator = auth()->user();

                        if (! $creator) {
                            Notification::make()
                                ->title('Unable to issue solo endorsement')
                                ->danger()
                                ->send();

                            return;
                        }

                        if (EndorsementService::hasActiveSoloEndorsement($position, $account)) {
                            Notification::make()
                                ->title('Unable to issue solo endorsement')
                                ->body("An active solo endorsement already exists for {$position->callsign}.")
                                ->danger()
                                ->send();

                            return;
                        }

                        EndorsementService::createTemporary(
                            $position,
                            $account,
                            $creator,
                            (int) $data['days']
                        );

                        if (filled($data['notes'] ?? null)) {
                            $account->addNote(
                                'training',
                                "Solo endorsement issued for {$position->callsign} for {$data['days']} days. Notes: {$data['notes']}",
                                $creator->id
                            );
                        }

                        Notification::make()
                            ->title("Solo endorsement issued for {$position->callsign}")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Issue Solo Endorsement')
                    ->modalDescription(fn () => 'Issue a solo endorsement to '.$this->trainingPlace->account->name)
                    ->modalSubmitActionLabel('Issue Endorsement'),
            ])
            ->emptyStateHeading('No solo endorsements found');
    }

    private function getEndorsablePositions(): Collection
    {
        $position = $this->trainingPlace->trainingPosition->position;
        $suffix = Arr::last(explode('_', $position->callsign));
        $topDown = app(UKCP::class)->getTopDownPositionsForPosition($position->callsign);

        $query = Position::query()
            ->where('callsign', 'like', '%_'.$suffix)
            ->where('temporarily_endorsable', true);

        if ($topDown !== null) {
            $query->where(fn ($query) => $query
                ->whereIn('ukcp_position_id', $topDown->pluck('id')->filter())
                ->orWhereIn('callsign', $topDown->pluck('callsign')->filter()));
        }

        return $query->orderBy('callsign')->get()
            ->push($position)
            ->unique('id')
            ->sortBy('callsign')
            ->values();
    }

    public function render()
    {
        return view('livewire.training.training-place-solo-endorsement');
    }
}
