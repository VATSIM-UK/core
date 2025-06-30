<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PositionGroupResource\Pages;
use App\Filament\Resources\PositionGroupResource\RelationManagers\MembershipEndorsementRelationManager;
use App\Models\Atc\PositionGroup;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PositionGroupResource extends Resource
{
    protected static ?string $model = PositionGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Tier Endorsements';

    protected static ?string $navigationGroup = 'Mentoring';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('name'),
                Infolists\Components\TextEntry::make('positions.callsign'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('membership_endorsement_count')
                    ->label('Endorsed')
                    ->counts('membershipEndorsement'),
            ])
            ->defaultSort('name')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('name', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            MembershipEndorsementRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPositionGroups::route('/'),
            'view' => Pages\ViewPositionGroup::route('/{record}'),
        ];
    }
}
