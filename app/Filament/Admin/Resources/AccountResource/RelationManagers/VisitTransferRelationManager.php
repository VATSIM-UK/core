<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VisitTransferRelationManager extends RelationManager
{
    protected static string $relationship = 'visitTransferApplications';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return 'VT Applications';
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('type_string')->label('Type'),
                Tables\Columns\TextColumn::make('facility.name')->label('Facility'),
                Tables\Columns\BadgeColumn::make('status')->label('Status')->formatStateUsing(fn ($state, $record) => $record->status_string)
                    ->color(fn ($record) => $record->status_color),
                Tables\Columns\TextColumn::make('created_at')->label('Submitted At')->dateTime()->isoDateTimeFormat('lll'),
                Tables\Columns\TextColumn::make('updated_at')->label('Last Updated')->dateTime()->isoDateTimeFormat('lll'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make('View')
                    ->label('View Application')
                    ->url(fn ($record) => route('filament.app.resources.visit-transfer.visit-transfer-applications.view', ['record' => $record->id]))
                    ->color('primary')
                    ->visible(fn () => auth()->user()->can('vt.application.view.*')),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
