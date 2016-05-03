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

    public function applications(){
        return $this->hasMany(\App\Modules\Visittransfer\Models\Application::class);
    }

    public function removeTrainingSpace(){
        $this->guardAgainstRemovingSpacesFromNonTrainingFacility();

        $this->decrement("training_spaces");
    }

    private function guardAgainstRemovingSpacesFromNonTrainingFacility(){
        if($this->training_required == 0){
            // NO IDEA WHAT WE'RE DOING HERE YET.
        }
    }

}