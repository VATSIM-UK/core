<?php

namespace App\Models\Mship\Concerns;

use Laravel\Fortify\Fortify;

trait HasTwoFactor
{
    public function getMandatoryTwoFactorAttribute(): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->contains(fn ($role) => (bool) $role->two_factor_mandatory);
        }

        return $this->roles()
            ->where('two_factor_mandatory', true)
            ->exists();
    }

    public function requiresTwoFactorSetup(): bool
    {
        return $this->mandatory_two_factor && ! $this->hasEnabledTwoFactorAuthentication();
    }

    public function requiresTwoFactorChallenge(): bool
    {
        return $this->hasEnabledTwoFactorAuthentication();
    }

    public function twoFactorSecretKey(): ?string
    {
        if ($this->two_factor_secret === null) {
            return null;
        }

        return Fortify::currentEncrypter()->decrypt($this->two_factor_secret);
    }
}
