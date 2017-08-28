<?php

namespace App\Models\Mship\Concerns;

trait HasTeamSpeakRegistrations
{
    public function teamspeakRegistrations()
    {
        return $this->hasMany(\App\Models\TeamSpeak\Registration::class, 'account_id');
    }

    public function getNewTsRegistrationAttribute()
    {
        return $this->teamspeakRegistrations->filter(function ($reg) {
            return is_null($reg->dbid);
        })->first();
    }
}
