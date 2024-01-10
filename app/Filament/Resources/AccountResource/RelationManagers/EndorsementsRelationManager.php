<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use App\Models\Mship\Account\Endorsement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EndorsementsRelationManager extends RelationManager
{
    protected static string $relationship = 'endorsements';

    protected static ?string $inverseRelationship = 'account';

    protected static ?string $recordTitleAttribute = 'positionGroup.name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('position_group_id')
                    ->label('Endorsement')
                    ->required()
                    ->options(\App\Models\Atc\PositionGroup::unassignedFor($this->ownerRecord)->mapWithKeys(function (\App\Models\Atc\PositionGroup $model) {
                        return [$model->getKey() => str($model->name)];
                    }))
                    ->hiddenOn('edit'),

                // TODO: determine maximum time in advance.
                Forms\Components\DatePicker::make('expired_at')
                    ->native(false)
                    ->label('Expires')
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
            ->recordTitle(fn ($record) => "{$record->positionGroup->name} endorsement")
            ->columns([
                Tables\Columns\TextColumn::make('positionGroup.name')->label('Name'),
                // TODO: color on type
                Tables\Columns\TextColumn::make('type')->label('Type')->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Granted')->date(),
                Tables\Columns\TextColumn::make('expired_at')->label('Expires')->date()->default('-'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Add endorsement'),
            ])
            ->actions([
                // TODO: define permissions for both actions
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->type == 'Temporary'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->type == 'Permanent'),
            ]);
    }

    public static function createEndorsement(array $data, self $livewrite)
    {
        $creator = auth()->user();

        dd($creator);

        Endorsement::create([
            'account_id' => $livewrite->ownerRecord->id,
            'position_group_id' => $data['position_group_id'],
            'expired_at' => $data['expired_at'],
            'created_by' => $creator->id,
        ]);
    }
}
