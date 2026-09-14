<?php

namespace App\Filament\Training\Pages\MyTraining;

use Filament\Pages\Page;

class MyExams extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected string $view = 'filament.training.pages.my-training.my-exams';

    protected static string|\UnitEnum|null $navigationGroup = 'My Training';

    protected static ?string $navigationLabel = 'My Exams';

    protected static ?string $slug = 'my-training/exams';

    protected static ?int $navigationSort = 10;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('training.access') ?? false;
    }
}
