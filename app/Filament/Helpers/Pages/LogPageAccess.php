<?php

namespace App\Filament\Helpers\Pages;

use App\Models\AdminAccessLog;

trait LogPageAccess
{
    /**
     * Returns the name of the action to be logged.
     * Designed to be overridden by the class using this trait
     * when something other than the default return value is used.
     */
    protected function getLogActionName(): string
    {
        return 'View';
    }

    public function mount($record): void
    {
        parent::mount($record);

        AdminAccessLog::create([
            'accessor_account_id' => auth()->user()->id,
            'loggable_id' => $this->record->id,
            'loggable_type' => get_class($this->record),
            'action' => $this->getLogActionName(),
        ]);
    }
}
