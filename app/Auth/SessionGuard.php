<?php

declare(strict_types=1);

namespace App\Auth;

use Illuminate\Auth\SessionGuard as BaseSessionGuard;

class SessionGuard extends BaseSessionGuard
{
    // Keep the framework's cookie name so existing remember cookies keep working.
    public function getRecallerName()
    {
        return 'remember_'.$this->name.'_'.sha1(BaseSessionGuard::class);
    }

    protected function userFromRecaller($recaller)
    {
        if (! $recaller->valid() || $this->recallAttempted) {
            return null;
        }

        $this->recallAttempted = true;

        $this->viaRemember = ! is_null($user = $this->provider->retrieveByToken(
            $recaller->id(), $recaller->token()
        ));

        if (! $this->viaRemember) {
            return null;
        }

        // Accounts have no password, so the recaller is verified against the MAC of an empty string.
        return hash_equals($this->hashPasswordForCookie(''), $recaller->hash()) ? $user : null;
    }
}
