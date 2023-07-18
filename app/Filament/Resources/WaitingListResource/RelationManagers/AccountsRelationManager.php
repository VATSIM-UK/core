<?php

namespace App\Filament\Resources\WaitingListResource\RelationManagers;

use App\Models\Training\WaitingList\WaitingListStatus;
use AxonC\FilamentCopyablePlaceholder\Forms\Components\CopyablePlaceholder;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'eligibleAccounts';

    protected static ?string $recordTitleAttribute = 'id';

    protected $listeners = ['refreshWaitingList' => '$refresh'];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                CopyablePlaceholder::make('id')
                    ->label('CID')
                    ->content(fn ($record) => $record->id)
                    ->iconOnly(),

                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->placeholder('Add notes here'),

                Forms\Components\Fieldset::make('account_status')
                    ->label('Account Status')
                    ->schema(function ($record) {
                        return [
                            Forms\Components\Radio::make('status')
                                ->label('')
                                ->required()
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pivot.position')->getStateUsing(fn ($record) => $record->pivot->position ?? '-')->sortable(),
                Tables\Columns\TextColumn::make('account_id')->label('CID')->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Name'),
                Tables\Columns\TextColumn::make('pivot.created_at')->label('Added on')->dateTime('M dS Y'),
                Tables\Columns\IconColumn::make('pivot.atc_hour_check')->boolean()->label('Hour check')->getStateUsing(fn ($record) => $record->pivot->atc_hour_check),
                Tables\Columns\IconColumn::make('pivot.flags_check')->boolean()->label('Flags check')->getStateUsing(fn ($record) => (bool) Arr::get($record->pivot?->flags_status_summary, 'overall')),
                Tables\Columns\IconColumn::make('pivot.eligible')->boolean(),
                Tables\Columns\BadgeColumn::make('pivot.status')->enum([
                    'Active' => 'Active',
                    'Deferred' => 'Deferred',
                ])->getStateUsing(fn ($record) => $record->pivot->current_status->name)->colors([
                    'success' => static fn ($record) => $record === 'Active',
                    'danger' => static fn ($record) => $record === 'Deferred',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function ($record, $data) {
                        $record->pivot->update([
                            'notes' => $data['notes'],
                        ]);

                        $status = $data['status'] == WaitingListStatus::DEFAULT_STATUS ? WaitingListStatus::DEFAULT_STATUS : WaitingListStatus::DEFERRED;
                        $status = WaitingListStatus::find($status);
                        $record->pivot->addStatus($status);

                        $flagsById = collect(Arr::get($data, 'flags', []));
                        // only update manual flags
                        $flagsToUpdate = $record->pivot->flags->filter(fn ($flag) => $flag->endorsement_id == null);
                        $flagsToUpdate->each(fn ($flag) => $flagsById->get($flag->id) ? $flag->pivot->mark() : $flag->pivot->unMark());

                        $record->pivot->flags()->sync(
                            $flagsById->mapWithKeys(fn ($value, $key) => [$key => ['marked_at' => $value ? now() : null]])->all(),
                        );

                        return $record;
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\DetachAction::make()
                    ->label('Remove')
                    ->successNotificationTitle('User removed from waiting list'),
            ]);
    }

    public static function canViewForRecord(Model $ownerRecord): bool
    {
        return auth()->user()->can('view', $ownerRecord);
    }

    protected function canView(Model $record): bool
    {
        return true;
    }

    protected function canEdit(Model $record): bool
    {
        return auth()->user()->can('updateAccounts', $this->ownerRecord);
    }

    protected function canDetach(Model $record): bool
    {
        return auth()->user()->can('removeAccount', $this->ownerRecord);
    }
}
