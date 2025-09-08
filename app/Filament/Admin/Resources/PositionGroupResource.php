<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PositionGroupResource\Pages\ListPositionGroups;
use App\Filament\Admin\Resources\PositionGroupResource\Pages\ViewPositionGroup;
use App\Filament\Admin\Resources\PositionGroupResource\RelationManagers\MembershipEndorsementRelationManager;
use App\Models\Atc\PositionGroup;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PositionGroupResource extends Resource
{
    protected static ?string $model = PositionGroup::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Tier Endorsements';

    protected static string|\UnitEnum|null $navigationGroup = 'Mentoring';

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('positions.callsign'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name')
                    ->sortable(),
                TextColumn::make('membership_endorsement_count')
                    ->label('Endorsed')
                    ->counts('membershipEndorsement'),
            ])
            ->defaultSort('name')
            ->recordActions([
                ViewAction::make(),
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
            'index' => ListPositionGroups::route('/'),
            'view' => ViewPositionGroup::route('/{record}'),
        ];
    }
}
