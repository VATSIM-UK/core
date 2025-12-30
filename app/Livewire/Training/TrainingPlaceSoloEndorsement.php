<?php

namespace App\Livewire\Training;

use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Services\Training\EndorsementService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
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
            ->description('Solo endorsements displayed are scoped only to the positions relevant to this training place and filtered by the commencement of the training place.')
            ->queryStringIdentifier('solo-endorsements')
            ->query(EndorsementService::getSoloEndorsementsForTrainingPlace($this->trainingPlace))
            ->defaultSort('created_at', 'desc')
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
            ->emptyStateHeading('No solo endorsements found');
    }

    public function render()
    {
        return view('livewire.training.training-place-solo-endorsement');
    }
}
