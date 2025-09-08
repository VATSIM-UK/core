<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use App\Enums\QualificationTypeEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QualificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'qualifications';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code'),
                TextColumn::make('name_long')->label('Name'),
                TextColumn::make('created_at')->since()->description(fn ($record) => $record->created_at)->label('Awarded')->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->options(collect(QualificationTypeEnum::cases())->mapWithKeys(fn ($enum) => [$enum->value => $enum->name]))->multiple(),
            ])->defaultSort('created_at');
    }
}
