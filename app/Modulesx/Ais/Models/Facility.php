<?php

namespace App\Modules\Ais\Models;

use App\Models\aModel;

/**
 * App\Modules\Ais\Models\Facility
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Facility\Position[] $positions
 */
class Facility extends aModel {

    protected $table = "ais_facility";
    protected $fillable = [
        "name"
    ];

    public function positions(){
        return $this->hasMany(Facility\Position::class);
    }
}