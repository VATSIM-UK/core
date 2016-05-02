<?php

namespace App\Modules\Visittransfer\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Ais\Models\Fir
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Aerodrome[] $airfields
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Fir\Sector[] $sectors
 */
class Facility extends Model {

    protected $table = "vt_facility";
    public $timestamps = false;

    public static function scopeTrainingRequired($query){
        return $query->where("training_required", "=", 1);
    }

}