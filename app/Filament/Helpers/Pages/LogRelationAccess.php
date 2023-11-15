<?php

namespace App\Filament\Helpers\Pages;

use App\Models\AdminAccessLog;

trait LogRelationAccess
{
    /**
     * Returns the name of the action to be logged.
     * Designed to be overridden by the class using this trait
     * when something other than the default return value is used.
     */
    protected function getLogActionName(): string
    {
        return 'ViewRelation';
    }

    public function mount(): void
    {
        parent::mount();

        AdminAccessLog::create([
            'accessor_account_id' => auth()->user()->id,
            'loggable_id' => $this->ownerRecord->id,
            'loggable_type' => get_class($this->ownerRecord),
            'action' => $this->getLogActionName(),
        ]);
    }
}
