<?php

namespace App\Filament\Training\Pages\MyTraining;

use Filament\Pages\Page;

class MyExamHistory extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.training.pages.my-training.my-exam-history';

    protected static string|\UnitEnum|null $navigationGroup = 'My Training';

    protected static ?string $navigationLabel = 'My Exam History';

    protected static ?int $navigationSort = 20;

    public static function canAccess(): bool
    {
        // Temporary beta permission
        if (! app()->runningUnitTests() && ! auth()->user()?->can('training.beta')) {
            return false;
        }

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
