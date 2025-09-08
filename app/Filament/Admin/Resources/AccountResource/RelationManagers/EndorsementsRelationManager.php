<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account\Endorsement;
use App\Models\Mship\Qualification;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EndorsementsRelationManager extends RelationManager
{
    protected static string $relationship = 'endorsements';

    protected static ?string $inverseRelationship = 'account';

    protected static ?string $recordTitleAttribute = 'endorsable.name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('position_group_id')
                    ->label('Endorsement')
                    ->required()
                    ->options(PositionGroup::unassignedFor($this->ownerRecord)->mapWithKeys(function (PositionGroup $model) {
                        return [$model->getKey() => str($model->name)];
                    }))
                    ->hiddenOn('edit'),

                DatePicker::make('expires_at')
                    ->native(false)
                    ->label('Expiration')
                    ->minDate(now()),

                Hidden::make('created_by')
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
                TextColumn::make('created_at')->label('Granted')->date(),
                TextColumn::make('expires_at')->label('Expires')->date()->default(''),
                TextColumn::make('duration')->label('Duration (Days)')
                    ->summarize(Sum::make()->hidden(fn (Builder $query): bool => ! $query->exists())
                        ->label('Total Duration')),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make()->label('Add endorsement'),
            ])
            ->groups([
                Group::make('endorsable_id')
                    ->label('Name')
                    ->getTitleFromRecordUsing(fn ($record): string => "$record->type - {$record->endorsable->name}")
                    ->groupQueryUsing(fn (\Illuminate\Database\Query\Builder $query) => $query->groupByRaw("CONCAT(endorsable_type,'::',endorsable_id)"))
                    ->getKeyFromRecordUsing(fn (Endorsement $record): string => "$record->endorsable_type::$record->endorsable_id")
                    ->titlePrefixedWithLabel(false),
            ])
            ->defaultGroup('endorsable_id')
            ->groupingSettingsHidden()
            ->recordActions([
                EditAction::make()
                    ->visible(fn ($record) => $record->expires_at),
                DeleteAction::make(),
            ])
            ->filters([
                SelectFilter::make('endorsable_type')
                    ->label('Type')
                    ->options([
                        PositionGroup::class => 'Tier Endorsement',
                        Position::class => 'Solo Endorsement',
                        Qualification::class => 'Rating Endorsement',
                    ])
                    ->multiple(),
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
