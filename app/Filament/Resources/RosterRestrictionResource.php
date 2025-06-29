<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RosterRestrictionResource\Pages;
use App\Models\Roster;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RosterRestrictionResource extends Resource
{
    protected static ?string $model = Roster::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Roster Restrictions';

    protected static ?string $navigationGroup = 'User Management';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user->can('roster.restriction.create') || $user->can('roster.restriction.remove');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('account.id'),
                TextColumn::make('account.name')->label('Name'),
                TextColumn::make('restrictionNote.content')->wrap(),
                TextColumn::make('restrictionNote.writer.name')
                    ->label('Created By'),
                TextColumn::make('restrictionNote.created_at')
                    ->label('Created At')
                    ->isoDateTimeFormat('lll'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNotNull('restriction_note_id');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRosterRestrictions::route('/'),
        ];
    }
}
