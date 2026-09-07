<?php

namespace App\Listeners\Mship;

use App\Notifications\Mship\RecoveryCodeUsed;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Events\RecoveryCodeReplaced;

class NotifyOfRecoveryCodeUse
{
    /**
     * Flag the current session as having just consumed a recovery code, so the
     * post-login response can tell this apart from an authenticator-code login
     * without trusting the raw `recovery_code` request field (which may be
     * present but invalid on a request that actually authenticated via `code`).
     */
    public function handle(RecoveryCodeReplaced $event): void
    {
        Session::put('two_factor.recovery_code_used', true);

        $event->user->notify(new RecoveryCodeUsed);
    }
}
