<?php

namespace App\Listeners\Mship;

use App\Notifications\Mship\RecoveryCodeUsed;
use Laravel\Fortify\Events\RecoveryCodeReplaced;

class NotifyOfRecoveryCodeUse
{
    public function handle(RecoveryCodeReplaced $event): void
    {
        $event->user->notify(new RecoveryCodeUsed);
    }
}
