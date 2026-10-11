<?php

namespace App\Models\Mship\Concerns;

use App\Models\Mship\Account\AvailabilitySetting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasAvailabilitySettings
{
    public function availabilitySetting(): HasOne
    {
        return $this->hasOne(AvailabilitySetting::class, 'account_id');
    }

    public function getAvailabilityDefaultsAttribute(): array
    {
        $setting = $this->availabilitySetting;

        return [
            'from' => $setting?->from ? Carbon::parse($setting->from)->format('H:i') : AvailabilitySetting::DEFAULT_FROM,
            'to' => $setting?->to ? Carbon::parse($setting->to)->format('H:i') : AvailabilitySetting::DEFAULT_TO,
        ];
    }

    public function setAvailabilityDefaults(string $from, string $to): void
    {
        $from = Carbon::parse($from)->format('H:i');
        $to = Carbon::parse($to)->format('H:i');

        if ($from === AvailabilitySetting::DEFAULT_FROM && $to === AvailabilitySetting::DEFAULT_TO) {
            $this->availabilitySetting()->delete();
            $this->unsetRelation('availabilitySetting');

            return;
        }

        $this->availabilitySetting()->updateOrCreate([], [
            'from' => $from,
            'to' => $to,
        ]);

        $this->unsetRelation('availabilitySetting');
    }
}
