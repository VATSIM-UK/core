<?php

namespace App\Filament\Training\Pages\Exam;

use App\Filament\Training\Pages\Exam\Actions\SetupExamAction;
use Filament\Pages\Page;

class Exams extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.training.pages.exams';

    protected static ?int $navigationSort = 10;

    protected static string|\UnitEnum|null $navigationGroup = 'Exams';

    public static function canAccess(): bool
    {
        return auth()->user()->can('training.exams.access');
    }

    protected function getHeaderActions(): array
    {
        return [
            SetupExamAction::make(),
        ];
    }
}
