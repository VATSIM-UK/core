<?php

declare(strict_types=1);

namespace App\Filament\Training\Concerns;

trait InteractsWithTrainingConductAutosave
{
    public bool $hasUnsavedChanges = false;

    public bool $isSaving = false;

    public ?int $lastChangedAt = null;

    public int $autosaveIdleSeconds = 1;

    public int $autosaveMinInterval = 5;

    public ?int $lastAutosaveAt = null;

    public function autosave(): void
    {
        if ($this->isSaving || ! $this->hasUnsavedChanges) {
            return;
        }

        $now = now()->timestamp;

        if ($this->lastChangedAt && ($now - $this->lastChangedAt) < $this->autosaveIdleSeconds) {
            return;
        }

        if ($this->lastAutosaveAt && ($now - $this->lastAutosaveAt) < $this->autosaveMinInterval) {
            return;
        }

        $this->lastAutosaveAt = $now;
        $this->save(withNotification: false);
    }

    public function markDirty(bool $skipRender = false): void
    {
        $this->hasUnsavedChanges = true;
        $this->lastChangedAt = now()->timestamp;

        if ($skipRender) {
            $this->skipRender();
        }
    }
}
