<?php

namespace App\Filament\Training\Pages\Mentor;

use App\Models\Cts\Session;
use Filament\Pages\Page;

class Mentoring extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.training.pages.mentoring';

    protected static ?int $navigationSort = 10;

    protected static string|\UnitEnum|null $navigationGroup = 'Mentoring';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('viewAny', Session::class) ?? false;
    }
}
