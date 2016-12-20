<?php

namespace App\Modules\Ais\Models\Facility;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table      = 'ais_facility_position';
    protected $primaryKey = 'id';
    public    $timestamps = true;
    public    $dates      = ['created_at', 'updated_at', 'deleted_at'];
    public    $fillable   = [
        "facility_id",
        "callsign_primary",
        "callsign_secondary",
        "callsign_format",
        "frequency",
        "logon_order"
    ];
}
