<?php

namespace App\Models\TeamSpeak;

use Illuminate\Database\Eloquent\Model;

class AtcServerGroup extends Model
{
    protected $table = 'teamspeak_atc_server_groups';

    protected $fillable = ['callsign', 'ts_sgid'];

    public function assignments()
    {
        return $this->hasMany(AtcGroupAssignment::class);
    }

    public function isEmpty(): bool
    {
        return $this->assignments()->doesntExist();
    }
}
