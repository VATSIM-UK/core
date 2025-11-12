<?php

namespace App\Filament\Training\Pages\Exam;

use Filament\Pages\Page;

class Exams extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.training.pages.exams';

    protected static ?string $navigationGroup = 'Exams';

    public static function canAccess(): bool
    {
        return auth()->user()->can('training.exams.access');
    }
}
