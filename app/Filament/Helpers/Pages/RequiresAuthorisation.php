<?php

namespace App\Filament\Helpers\Pages;

trait RequiresAuthorisation
{
    abstract protected static function canUse(): bool;

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
