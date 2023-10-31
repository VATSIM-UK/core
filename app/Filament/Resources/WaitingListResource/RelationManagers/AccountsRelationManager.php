<?php

namespace App\Filament\Resources\WaitingListResource\RelationManagers;

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
                Forms\Components\Fieldset::make('account_status_fieldset')
                    ->label('Account Status')
                    ->schema(function ($record) {
                        return [
                            Forms\Components\Radio::make('account_status')
                                ->label('Status')
                                ->options([
                                    WaitingListStatus::DEFAULT_STATUS => 'Active',
                                    WaitingListStatus::DEFERRED => 'Deferred',
                                ])
                                ->afterStateHydrated(fn ($component, $state) => $component->state($record->pivot->current_status->id)),
                        ];
                    }),
                Forms\Components\Fieldset::make('automatic_flags')
                    ->label('Automatic Flags')
                    ->schema(function ($record) {
                        return $record->pivot->flags->filter(fn ($flag) => $flag->endorsement_id != null)->map(function ($flag) {
                            return Forms\Components\Toggle::make('flags.'.$flag->id)
                                ->disabled()
                                ->label($flag->name)
                                ->afterStateHydrated(fn ($component, $state) => $component->state((bool) $flag->pivot->value));
                        })->all();
                    })
                    ->visible(fn ($record) => $record->pivot->flags->filter(fn ($flag) => $flag->endorsement_id != null)->isNotEmpty()),

                Forms\Components\Fieldset::make('cts_theory_exam')
                    ->label('CTS Theory Exam')
                    ->schema(function ($record) {
                        return [
                            Forms\Components\Toggle::make('cts_theory_exam')
                                ->label('Passed')
                                ->afterStateHydrated(fn ($component, $state) => $component->state((bool) $record->pivot->theory_exam_passed))
                                ->readonly(),
                        ];
                    })
                    ->visible(fn ($record) => $record->waitingList->feature_toggles['check_cts_theory_exam'] ?? true),

                Forms\Components\Fieldset::make('manual_flags')
                    ->label('Manual Flags')
                    ->schema(function ($record) {
                        return $record->pivot->flags->filter(fn ($flag) => $flag->endorsement_id == null)->map(function ($flag) {
                            return Forms\Components\Toggle::make('flags.'.$flag->id)
                                ->label($flag->name)
                                ->afterStateHydrated(fn ($component, $state) => $component->state((bool) $flag->pivot->value));
                        })->all();
                    })
                    ->visible(fn ($record) => $record->pivot->flags->isNotEmpty()),

                Forms\Components\Fieldset::make('eligibility_summary')
                    ->label('Eligibility Breakdown')
                    ->schema(function ($record) {
                        return [
                            Forms\Components\Toggle::make('base_controlling_hours')
                                ->label('Controlling Hours')
                                ->disabled()
                                ->afterStateHydrated(fn ($component, $state) => $component->state(Arr::get($record->pivot->eligibility_summary, 'base_controlling_hours'))),

                            Forms\Components\Toggle::make('flags_check')
                                ->label('Flags Check')
                                ->disabled()
                                ->afterStateHydrated(fn ($component, $state) => $component->state((bool) Arr::get($record->pivot->flags_status_summary, 'overall'))),

                            Forms\Components\Toggle::make('status')
                                ->label('Status')
                                ->disabled()
                                ->afterStateHydrated(fn ($component, $state) => $component->state((bool) $record->pivot->current_status->name == 'Active')),
                        ];
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pivot.position')->getStateUsing(fn ($record) => $record->pivot->position ?? '-')->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('pivot_created_at', $direction))->label('Position'),
                Tables\Columns\TextColumn::make('account_id')->label('CID')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Name')->searchable(['name_first', 'name_last']),
                Tables\Columns\TextColumn::make('pivot.created_at')->label('Added on')->dateTime('M dS Y'),
                Tables\Columns\IconColumn::make('pivot.cts_theory_exam')->boolean()->label('CTS Theory Exam')->getStateUsing(fn ($record) => $record->pivot->theory_exam_passed)->visible(fn ($record) => $record->waitingList->feature_toggles['check_cts_theory_exam'] ?? true),
                Tables\Columns\IconColumn::make('pivot.atc_hour_check')->boolean()->label('Hour check')->getStateUsing(fn ($record) => Arr::get($record->pivot?->eligibility_summary, 'base_controlling_hours')),
                Tables\Columns\IconColumn::make('pivot.flags_check')->boolean()->label('Flags check')->getStateUsing(fn ($record) => (bool) Arr::get($record->pivot?->flags_status_summary, 'overall')),
                Tables\Columns\IconColumn::make('pivot.eligible')->boolean()->label('Eligible')->getStateUsing(fn ($record) => $record->pivot->eligible),
                Tables\Columns\TextColumn::make('pivot.status')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->pivot->current_status->name)->colors([
                        'success' => 'Active',
                        'gray' => 'Deferred',
                    ])->label('Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function ($record, $data, $livewire) {
                        $record->pivot->update([
                            'notes' => $data['notes'],
                        ]);

                        $status = $data['account_status'] == WaitingListStatus::DEFAULT_STATUS ? WaitingListStatus::DEFAULT_STATUS : WaitingListStatus::DEFERRED;
                        $status = WaitingListStatus::find($status);
                        $record->pivot->addStatus($status);

                        $flagsById = collect(Arr::get($data, 'flags', []));
                        // only update manual flags
                        $flagsToUpdate = $record->pivot->flags->filter(fn ($flag) => $flag->endorsement_id == null);
                        $flagsToUpdate->each(fn ($flag) => $flagsById->get($flag->id) ? $flag->pivot->mark() : $flag->pivot->unMark());

                        $record->pivot->flags()->sync(
                            $flagsById->mapWithKeys(fn ($value, $key) => [$key => ['marked_at' => $value ? now() : null]])->all(),
                        );

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
