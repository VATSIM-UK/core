<?php

namespace App\Modules\Networkdata\Traits;

trait NetworkDataAccount
{
    /**
     * Fetch all ATC Sessions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function networkDataAtc()
    {
        return $this->hasMany(\App\Modules\NetworkData\Models\Atc::class, 'account_id', 'id');
    }

    /**
     * Get the member's current ATC session, if connected.
     *
     * @return \App\Modules\NetworkData\Models\Atc
     */
    public function networkDataAtcCurrent()
    {
        return $this->hasOne(\App\Modules\NetworkData\Models\Atc::class, 'account_id', 'id')
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
