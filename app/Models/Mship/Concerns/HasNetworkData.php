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
        return $this->networkDataAtcCurrent->exists;
    }

    /*
     * Fetch all Pilot Sessions
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
//    public function networkDataPilot()
//    {
//        return $this->hasMany(\App\Modules\NetworkData\Models\Pilot::class, "account_id", "id");
//    }
}
