<?php

namespace App\Models\Mship\Concerns;

use App\Enums\EmailType;
use App\Models\Mship\Account\EmailSetting;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasEmailSettings
{
    public function emailSettings(): HasMany
    {
        return $this->hasMany(EmailSetting::class, 'account_id');
    }

    public function isEmailEnabled(EmailType $type): bool
    {
        $setting = $this->emailSettings()
            ->where('email_type', $type->value)
            ->first();

        if (! $setting) {
            return true;
        }

        return $setting->enabled;
    }

    public function setEmailEnabled(EmailType $type, bool $enabled): void
    {
        if ($enabled) {
            $this->emailSettings()
                ->where('email_type', $type->value)
                ->delete();
        } else {
            $this->emailSettings()->updateOrCreate(
                ['email_type' => $type->value],
                ['enabled' => false]
            );
        }
    }

    public function setEmailPreferences(array $preferences): void
    {
        foreach ($preferences as $key => $enabled) {
            if ($key instanceof EmailType) {
                $type = $key;
            } elseif (is_string($key)) {
                $type = EmailType::tryFrom($key);
            } else {
                continue;
            }

            if ($type instanceof EmailType) {
                $this->setEmailEnabled($type, (bool) $enabled);
            }
        }
    }
}
