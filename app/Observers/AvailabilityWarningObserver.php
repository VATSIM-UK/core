<?php

namespace App\Observers;

use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Notifications\Training\AvailabilityWarningCreated;

class AvailabilityWarningObserver
{
    /**
     * Handle the AvailabilityWarning "created" event.
     * This runs within the same database transaction as the model creation,
     * ensuring atomicity between the database insert and notification.
     */
    public function created(AvailabilityWarning $availabilityWarning): void
    {
        // Load the relationships to ensure they're available
        $availabilityWarning->load('trainingPlace.waitingListAccount.account');

        // Get the account associated with this training place
        $account = $availabilityWarning->trainingPlace->waitingListAccount->account;

        // Send the notification to the account
        $account->notify(new AvailabilityWarningCreated($availabilityWarning));
    }
}
