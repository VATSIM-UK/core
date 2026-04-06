<?php

namespace App\Filament\Training\Pages\MyTraining;

use Filament\Pages\Page;

class MyExamHistory extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.training.pages.my-training.my-exam-history';

    protected static string|\UnitEnum|null $navigationGroup = 'My Training';

    protected static ?string $navigationLabel = 'My Exam History';

    public static function canAccess(): bool
    {
        return auth()->user()->can('training.access') ?? false;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            Widgets\MyPracticalExamHistoryTable::class,
            Widgets\MyTheoryExamHistoryTable::class,
        ];
    }
}
