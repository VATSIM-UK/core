<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TheoryManagementResource\Pages;
use App\Filament\Resources\TheoryManagementResource\RelationManagers\TheoryQuestionsManager;
use App\Models\Cts\TheoryManagement;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TheoryManagementResource extends Resource
{
    protected static ?string $model = TheoryManagement::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $label = 'Theory Management';

    protected static ?string $pluralLabel = 'Theory Management';

    protected static ?string $navigatonLabel = 'Theory Management';

    protected static ?string $navigationGroup = 'Mentoring';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rating')->label('Rating'),
                TextColumn::make('questions')->label('No. Questions'),
                TextColumn::make('passmark')->label('Passmark'),
                TextColumn::make('time_allowed')->label('Time Allowed'),
                TextColumn::make('enabled')
                    ->label('Enabled')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->enabled ? 'Enabled' : 'Disabled')
                    ->color(fn ($record) => $record->enabled ? 'success' : 'danger')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        0 => 'ATC',
                        1 => 'Pilot',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTheoryManagement::route('/'),
            // 'create' => Pages\CreateTheoryManagement::route('/create'),
            'edit' => Pages\EditTheoryManagement::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            TheoryQuestionsManager::class,
        ];
    }
}
