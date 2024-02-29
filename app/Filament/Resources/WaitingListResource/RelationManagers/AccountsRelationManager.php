<?php

namespace App\Filament\Resources\WaitingListResource\RelationManagers;

use App\Models\Roster;
use App\Models\Training\WaitingList\WaitingListStatus;
use AxonC\FilamentCopyablePlaceholder\Forms\Components\CopyablePlaceholder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'eligibleAccounts';

    protected static ?string $recordTitleAttribute = 'id';

    protected $listeners = ['refreshWaitingList' => '$refresh'];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('base_information')
                    ->label('Base Information')
                    ->schema([
                        CopyablePlaceholder::make('id')
                            ->label('CID')
                            ->content(fn ($record) => $record->id)
                            ->iconOnly(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Add notes here'),

                    ]),
                Forms\Components\Fieldset::make('cts_theory_exam')
                    ->label('CTS Theory Exam')
                    ->schema(function ($record) {
                        return [
                            Forms\Components\Toggle::make('cts_theory_exam')
                                ->label('Passed')
                                ->afterStateHydrated(fn ($component, $state) => $component->state((bool) $record->pivot->theory_exam_passed))
                                ->disabled(),
                        ];
                    })
                    ->visible(fn ($record) => $record->waitingList->feature_toggles['check_cts_theory_exam'] ?? true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pivot.position')->getStateUsing(fn ($record) => $record->pivot->position ?? '-')->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('pivot_created_at', $direction))->label('Position'),
                Tables\Columns\TextColumn::make('account_id')->label('CID')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Name')->searchable(['name_first', 'name_last']),
                Tables\Columns\TextColumn::make('pivot.created_at')->label('Added on')->dateTime('d/m/Y'),
                Tables\Columns\IconColumn::make('pivot.cts_theory_exam')->boolean()->label('CTS Theory Exam')->getStateUsing(fn ($record) => $record->pivot->theory_exam_passed)->visible(fn ($record) => $record->waitingList->feature_toggles['check_cts_theory_exam'] ?? true),
                Tables\Columns\IconColumn::make('on_roster')->boolean()->label('On roster')->getStateUsing(fn ($record) => Roster::where('account_id', $record->id)->exists()),
                Tables\Columns\IconColumn::make('pivot.eligible')->boolean()->label('Eligible')->getStateUsing(fn ($record) => $record->pivot->eligible),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function ($record, $data, $livewire) {
                        $record->pivot->update([
                            'notes' => $data['notes'],
                        ]);

                        $livewire->dispatch('refreshWaitingList');

                        return $record;
                    })
                    ->visible(fn ($record) => $this->can('updateAccounts', $record->pivot->waitingList)),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\DetachAction::make()
                    ->label('Remove')
                    ->using(fn ($record, $livewire) => $livewire->ownerRecord->removeFromWaitingList($record))
                    ->successNotificationTitle('User removed from waiting list'),
            ])->defaultSort('pivot_created_at', 'asc')->persistSearchInSession()->defaultPaginationPageOption(25);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->can('view', $ownerRecord);
    }

    protected function canView(Model $record): bool
    {
        return true;
    }

    protected function canEdit(Model $record): bool
    {
        return $this->can('updateAccounts', $this->getOwnerRecord());
    }

    protected function canDetach(Model $record): bool
    {
        return $this->can('removeAccount', $this->getOwnerRecord());
    }
}
