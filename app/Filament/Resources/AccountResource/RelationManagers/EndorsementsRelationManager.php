<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account\Endorsement;
use App\Models\Mship\Qualification;
use Filament\Forms;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EndorsementsRelationManager extends RelationManager
{
    protected static string $relationship = 'endorsements';

    protected static ?string $inverseRelationship = 'account';

    protected static ?string $recordTitleAttribute = 'endorsable.name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('position_group_id')
                    ->label('Endorsement')
                    ->required()
                    ->options(PositionGroup::unassignedFor($this->ownerRecord)->mapWithKeys(function (PositionGroup $model) {
                        return [$model->getKey() => str($model->name)];
                    }))
                    ->hiddenOn('edit'),

                Forms\Components\DatePicker::make('expires_at')
                    ->native(false)
                    ->label('Expiration')
                    ->minDate(now()),

                Forms\Components\Hidden::make('created_by')
                    ->afterStateHydrated(fn ($component, $state) => $component->state(auth()->user()->getKey())),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn ($record) => "{$record->endorsable->name} endorsement")
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Granted')->date(),
                Tables\Columns\TextColumn::make('expires_at')->label('Expires')->date()->default(''),
                Tables\Columns\TextColumn::make('duration')->label('Duration (Days)')
                    ->summarize(
                        Sum::make()
                            ->label('Days')
                            ->hidden(fn (Builder $query): bool => $query->whereNull('expires_at')->exists())
                    )
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make()->label('Add endorsement'),
            ])
            ->groups([
                Group::make('endorsable_id')
                    ->label('Name')
                    ->getTitleFromRecordUsing(fn ($record): string => "$record->type - {$record->endorsable->name}")
                    ->titlePrefixedWithLabel(false)
                    ->groupQueryUsing(function (\Illuminate\Database\Query\Builder $query) {
                        return $query->groupBy('endorsable_id');
                    })
            ])
            ->defaultGroup('endorsable_id')
            ->groupingSettingsHidden()
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->expires_at),
                Tables\Actions\DeleteAction::make(),
            ])
            ->filters([
                SelectFilter::make('endorsable_type')
                    ->label('Type')
                    ->options([
                        PositionGroup::class => 'Tier Endorsement',
                        Position::class => 'Solo Endorsement',
                        Qualification::class => 'Rating Endorsement',
                    ])
                    ->multiple()
            ]);
    }

    public static function createEndorsement(array $data, self $livewire)
    {
        $creator = auth()->user();

        Endorsement::create([
            'account_id' => $livewire->ownerRecord->id,
            'position_group_id' => $data['position_group_id'],
            'expires_at' => $data['expires_at'],
            'created_by' => $creator->id,
        ]);
    }
}
