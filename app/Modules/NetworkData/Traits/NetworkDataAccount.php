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
