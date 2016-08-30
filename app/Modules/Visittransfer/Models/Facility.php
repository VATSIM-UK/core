<?php

namespace App\Modules\Visittransfer\Models;

use App\Modules\Visittransfer\Exceptions\Facility\DuplicateFacilityNameException;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Ais\Models\Fir
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Aerodrome[]  $airfields
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Fir\Sector[] $sectors
 */
class Facility extends Model
{

    protected $table      = "vt_facility";
    protected $primaryKey = "id";
    public    $timestamps = false;
    public    $fillable   = [
        "name",
        "description",
        "can_visit",
        "can_transfer",
        "training_required",
        "training_team",
        "training_spaces",
        "stage_statement_enabled",
        "stage_reference_enabled",
        "stage_reference_quantity",
        "stage_checks",
        "auto_acceptance",
    ];

    public static function create(array $attributes = [])
    {
        (new Facility)->guardAgainstDuplicateFacilityName(array_get($attributes, "name", ""));

        return parent::create($attributes);
    }

    public function update(array $attributes = [], array $options = [])
    {
        (new Facility)->guardAgainstDuplicateFacilityName(array_get($attributes, "name", ""), $this->id);

        if(strcasecmp(array_get($attributes, "training_spaces", null), "null") == 0){
            $attributes['training_spaces'] = null;
        }

        return parent::update($attributes, $options);
    }

    public static function scopeAtc($query)
    {
        return $query->where("training_team", "=", "atc");
    }

    public static function scopePilot($query)
    {
        return $query->where("training_team", "=", "pilot");
    }

    public static function scopeCanVisit($query)
    {
        return $query->where("can_visit", "=", "1");
    }

    public static function scopeOnlyVisit($query)
    {
        return $query->where("can_visit", "=", "1")->where("can_transfer", "=", "0");
    }

    public static function scopeCanTransfer($query)
    {
        return $query->where("can_transfer", "=", "1")->trainingRequired();
    }

    public static function scopeOnlyTransfer($query)
    {
        return $query->where("can_visit", "=", "0")->where("can_transfer", "=", "1");
    }

    public static function scopeTrainingRequired($query)
    {
        return $query->where("training_required", "=", 1);
    }

    public function applications()
    {
        return $this->hasMany(\App\Modules\Visittransfer\Models\Application::class);
    }

    public function addTrainingSpace()
    {
        if ($this->training_required == 1 && $this->training_spaces !== null) {
            $this->increment("training_spaces");
        }
    }

    public function removeTrainingSpace()
    {
        if ($this->training_required == 1 && $this->training_spaces !== null) {
            $this->decrement("training_spaces");
        }
    }

    private function guardAgainstDuplicateFacilityName($proposedName, $excludeCurrent = false)
    {
        if ($excludeCurrent && Facility::where("id", "!=", $excludeCurrent)
                                       ->where("name", "LIKE", $proposedName)
                                       ->count() > 0
        ) {
            throw new DuplicateFacilityNameException($proposedName);
        }

        if (!$excludeCurrent && Facility::where("name", "LIKE", $proposedName)->count() > 0) {
            throw new DuplicateFacilityNameException($proposedName);
        }
    }
}