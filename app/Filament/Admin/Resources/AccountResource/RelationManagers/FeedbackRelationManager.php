<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use App\Filament\Admin\Resources\FeedbackResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FeedbackRelationManager extends RelationManager
{
    protected static string $relationship = 'feedback';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('form.slug'),
                Tables\Columns\IconColumn::make('actioned_at')->label('Actioned')->timestampBoolean(),
                Tables\Columns\IconColumn::make('sent_at')->label('Sent to User')->timestampBoolean(),
                Tables\Columns\TextColumn::make('created_at')->since(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->resource(FeedbackResource::class),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
