<?php

namespace App\Observers\Training;

use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Services\Training\TrainingPlaceService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class TrainingPlaceObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private TrainingPlaceService $trainingPlaceService
    ) {}

    /**
     * Handle the TrainingPlace "created" event.
     */
    public function created(TrainingPlace $trainingPlace): void
    {
        $this->trainingPlaceService->assignMentoringPermissions($trainingPlace);
    }

    /**
     * Handle the TrainingPlace "updated" event.
     */
    public function updated(TrainingPlace $trainingPlace): void
    {
        //
    }

    /**
     * Handle the TrainingPlace "deleted" event.
     */
    public function deleted(TrainingPlace $trainingPlace): void
    {
        $trainingPlace->deletePendingSessionRequests();
        $this->trainingPlaceService->revokeMentoringPermissions($trainingPlace);
    }

    /**
     * Handle the TrainingPlace "restored" event.
     */
    public function restored(TrainingPlace $trainingPlace): void
    {
        //
    }

    /**
     * Handle the TrainingPlace "force deleted" event.
     */
    public function forceDeleted(TrainingPlace $trainingPlace): void
    {
        //
    }
}
