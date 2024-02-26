<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use App\Models\Atc\PositionGroup;
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

                // TODO: determine maximum time in advance.
                Forms\Components\DatePicker::make('expires_at')
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
            ->recordTitle(fn ($record) => "{$record->endorsable->name} endorsement")
            ->columns([
                Tables\Columns\TextColumn::make('endorsable.name')->label('Name'),
                // TODO: color on type
                Tables\Columns\TextColumn::make('type')->label('Type')->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Granted')->date(),
                Tables\Columns\TextColumn::make('expires_at')->label('Expires')->date()->default(''),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make()->label('Add endorsement'),
            ])
            ->actions([
                // TODO: define permissions for both actions
                //                Tables\Actions\EditAction::make()
                //                    ->visible(fn ($record) => $record->type == 'Temporary'),
                //                Tables\Actions\DeleteAction::make()
                //                    ->visible(fn ($record) => $record->type == 'Permanent'),
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
