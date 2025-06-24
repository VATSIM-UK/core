<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TheoryManagementResource\Pages;
use App\Filament\Resources\TheoryManagementResource\RelationManagers\TheoryQuestionsManager;
use App\Models\Cts\TheoryManagement;
use Filament\Resources\Resource;

class TheoryManagementResource extends Resource
{
    protected static ?string $model = TheoryManagement::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $label = 'Theory Management';

    protected static ?string $pluralLabel = 'Theory Management';

    protected static ?string $navigatonLabel = 'Theory Management';

    protected static ?string $navigationGroup = 'Mentoring';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTheoryManagement::route('/'),
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
