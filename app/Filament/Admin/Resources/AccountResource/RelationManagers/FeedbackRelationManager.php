<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use App\Filament\Admin\Resources\FeedbackResource;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeedbackRelationManager extends RelationManager
{
    protected static string $relationship = 'feedback';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('form.slug'),
                IconColumn::make('actioned_at')->label('Actioned')->timestampBoolean(),
                IconColumn::make('sent_at')->label('Sent to User')->timestampBoolean(),
                TextColumn::make('created_at')->since(),
            ])
            ->recordActions([
                ViewAction::make()->resource(FeedbackResource::class),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
