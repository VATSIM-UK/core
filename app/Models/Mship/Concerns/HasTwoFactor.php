<?php

namespace App\Models\Mship\Concerns;

trait HasTwoFactor
{
    public function getMandatoryTwoFactorAttribute(): bool
    {
        return $this->roles()
            ->get()
            ->contains(fn ($role) => (bool) $role->two_factor_mandatory);
    }

    public function requiresTwoFactorSetup(): bool
    {
        return $this->mandatory_two_factor && ! $this->hasEnabledTwoFactorAuthentication();
    }

    public function requiresTwoFactorChallenge(): bool
    {
        return $this->hasEnabledTwoFactorAuthentication();
    }
}
