<?php

namespace App\Models\Mship\Concerns;

trait HasNetworkData
{
    /**
     * Fetch all ATC Sessions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function networkDataAtc()
    {
        return $this->hasMany(\App\Models\NetworkData\Atc::class, 'account_id', 'id');
    }

    /**
     * Fetch all ATC Sessions from within the UK.
     *
     * @return mixed
     */
    public function networkDataAtcUk()
    {
        return $this->networkDataAtc()->isUk();
    }

    /**
     * Get the member's current ATC session, if connected.
     *
     * @return \App\Models\NetworkData\Atc
     */
    public function networkDataAtcCurrent()
    {
        return $this->hasOne(\App\Models\NetworkData\Atc::class, 'account_id', 'id')
            ->whereNull('disconnected_at')
            ->limit(1);
    }

    /**
     * Determine if the user is on the network.
     *
     * @return bool
     */
    public function getIsOnNetworkAttribute()
    {
        return $this->networkDataAtcCurrent()->exists();
    }

    /*
     * Fetch all Pilot Sessions
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function networkDataPilot()
    {
        return $this->hasMany(\App\Models\NetworkData\Pilot::class, 'account_id', 'id');
    }

    /**
     * Fetch date of last ATC Session within the UK.
     *
     * @return \Carbon\Carbon
     */
    public function lastSeenControllingUK()
    {
        $lastSession = $this->networkDataAtcUk()
            ->orderByDesc('disconnected_at')
            ->first();

        return $lastSession?->disconnected_at;
    }
}
