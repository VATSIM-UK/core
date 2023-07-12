<?php

namespace App\Filament\Helpers\Pages;

use Filament\Pages\Page;

abstract class BasePage extends Page
{
    /**
     * Returns if the current user is able to see and use this page.
     *
     * Defaults to `true` unless overriden
     */
    protected static function canUse(): bool
    {
        return true;
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return static::canUse();
    }

    public function mount(): void
    {
        parent::mount();
        abort_unless(static::canUse(), 403);
    }
}
