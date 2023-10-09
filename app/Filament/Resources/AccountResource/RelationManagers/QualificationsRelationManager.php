<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use App\Enums\QualificationTypeEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;

class QualificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'qualifications';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('name_long')->label('Name'),
                Tables\Columns\TextColumn::make('created_at')->since()->description(fn ($record) => $record->created_at)->label('Awarded')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(collect(QualificationTypeEnum::cases())->mapWithKeys(fn ($enum) => [$enum->value => $enum->name]))->multiple(),
            ])->defaultSort('created_at');
    }
}
