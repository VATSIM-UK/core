<?php

namespace App\Observers\Training;

use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Services\Training\TrainingGroupRoleService;
use App\Services\Training\TrainingPlaceService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Log;

class TrainingPlaceObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private TrainingPlaceService $trainingPlaceService,
        private TrainingGroupRoleService $trainingGroupRoles,
    ) {}

    /**
     * Handle the TrainingPlace "created" event.
     */
    public function created(TrainingPlace $trainingPlace): void
    {
        $this->trainingPlaceService->assignMentoringPermissions($trainingPlace);
        $this->syncStudentRoles($trainingPlace);
    }

    /**
     * Handle the TrainingPlace "updated" event.
     */
    public function updated(TrainingPlace $trainingPlace): void
    {
        if ($trainingPlace->wasChanged(['trainable_type', 'trainable_id'])) {
            $this->syncStudentRoles($trainingPlace);
        }
    }

    /**
     * Handle the TrainingPlace "deleted" event.
     */
    public function deleted(TrainingPlace $trainingPlace): void
    {
        $trainingPlace->deletePendingSessionRequests();
        $this->trainingPlaceService->revokeMentoringPermissions($trainingPlace);
        $this->trainingPlaceService->removeCtsMembershipsForTrainingPlace($trainingPlace);
        $this->syncStudentRoles($trainingPlace);
    }

    /**
     * Handle the TrainingPlace "restored" event.
     */
    public function restored(TrainingPlace $trainingPlace): void
    {
        $this->trainingPlaceService->assignMentoringPermissions($trainingPlace);
        $this->syncStudentRoles($trainingPlace);
    }

    /**
     * Handle the TrainingPlace "force deleted" event.
     */
    public function forceDeleted(TrainingPlace $trainingPlace): void
    {
        //
    }

    private function syncStudentRoles(TrainingPlace $trainingPlace): void
    {
        $account = $trainingPlace->account;

        if (! $account instanceof Account) {
            Log::info('Student role sync skipped: account not found', [
                'training_place_id' => $trainingPlace->id,
                'account_id' => $trainingPlace->account_id,
            ]);

            return;
        }

        $this->trainingGroupRoles->syncStudentRoles($account);
    }
}
