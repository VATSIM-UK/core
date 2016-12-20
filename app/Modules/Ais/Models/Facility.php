<?php

namespace App\Modules\Ais\Models;

use App\Modules\Ais\Models\Facility\Position;
use App\Modules\NetworkData\Models\Atc;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $table      = 'ais_facility';
    protected $primaryKey = 'id';
    public    $timestamps = true;
    public    $dates      = ['created_at', 'updated_at', 'deleted_at'];
    public    $fillable   = [
        "name",
    ];

    public function positions(){
        return $this->hasMany(Position::class, "id", "facility_id");
    }

    public function airports(){
        return $this->belongsToMany(Airport::class, "ais_facility_to_airport", "facility_id", "airport_id");
    }

    public function networkDataAtc()
    {
        return $this->hasManyThrough(Atc::class, Position::class, "facility_id", "facility_position_id", "id");
    }
}
