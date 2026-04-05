<?php

namespace App\Filament\Admin\Resources\Accounts\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class VisitTransferRelationManager extends RelationManager
{
    protected static string $relationship = 'visitTransferApplications';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'VT Applications';
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('type_string')->label('Type'),
                TextColumn::make('facility.name')->label('Facility'),
                BadgeColumn::make('status')->label('Status')->formatStateUsing(fn ($state, $record) => $record->status_string)
                    ->color(fn ($record) => $record->status_color),
                TextColumn::make('created_at')->label('Submitted At')->dateTime()->isoDateTimeFormat('lll'),
                TextColumn::make('updated_at')->label('Last Updated')->dateTime()->isoDateTimeFormat('lll'),
            ])
            ->recordActions([
                ViewAction::make('View')
                    ->label('View Application')
                    ->url(fn ($record) => route('filament.app.resources.visit-transfer.visit-transfer-applications.view', ['record' => $record->id]))
                    ->color('primary')
                    ->visible(fn () => auth()->user()->can('vt.application.view.*')),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
