<?php

namespace App\Filament\Admin\Resources\Positions;

use App\Filament\Admin\Resources\Positions\Pages\CreatePosition;
use App\Filament\Admin\Resources\Positions\Pages\ListPositions;
use App\Models\Atc\Position;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PositionResource extends Resource
{
    protected static ?string $model = Position::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('operations.positions');
    }

    public static function getFormSchema(): array
    {
        return [
            TextInput::make('callsign')
                ->required()
                ->maxLength(191)
                ->unique(ignorable: fn (?Position $record): ?Position => $record),
            TextInput::make('name')
                ->required()
                ->maxLength(191),
            Grid::make(3)->schema([
                TextInput::make('frequency')
                    ->helperText('MHz (e.g. 123.450)')
                    ->required()
                    ->numeric()
                    ->step(0.001),
                Select::make('type')
                    ->required()
                    ->options(Position::typeOptions())
                    ->afterStateHydrated(function ($record, $set) {
                        if ($record) {
                            $set('type', $record->getRawOriginal('type'));
                        }
                    }),
                Select::make('positionGroups')
                    ->label('Position Groups')
                    ->relationship('positionGroups', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ])->columnSpanFull(),
            Toggle::make('sub_station')
                ->label('Sub Station'),
            Toggle::make('temporarily_endorsable')
                ->label('Temporarily Endorsable'),
            Toggle::make('virtual')
                ->label('Virtual'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema(static::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('callsign')
                    ->label('Callsign')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('frequency')
                    ->label('Frequency')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->sortable(),
                IconColumn::make('virtual')
                    ->label('Virtual')
                    ->boolean()
                    ->trueColor('warning')
                    ->falseColor('gray'),
                IconColumn::make('sub_station')
                    ->label('Sub Station')
                    ->boolean()
                    ->trueColor('info')
                    ->falseColor('gray'),
                IconColumn::make('temporarily_endorsable')
                    ->label('Temp. Endorsable')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('positionGroups.name')
                    ->label('Position Groups')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(Position::typeOptions()),
                TernaryFilter::make('virtual'),
                TernaryFilter::make('sub_station'),
                TernaryFilter::make('temporarily_endorsable'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('callsign');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPositions::route('/'),
            'create' => CreatePosition::route('/create'),
        ];
    }
}
