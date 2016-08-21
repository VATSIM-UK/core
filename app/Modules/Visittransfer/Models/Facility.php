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
    public    $timestamps = false;
    public    $fillable   = [
        "name",
        "description",
        "training_required",
        "training_team",
        "training_spaces",
        "stage_statement_enabled",
        "stage_reference_enabled",
        "stage_reference_quantity",
        "stage_checks",
        "auto_acceptance",
    ];

    public $attributes = [
        "training_required"        => 1,
        "training_spaces"          => 0,
        "stage_statement_enabled"  => 1,
        "stage_reference_enabled"  => 1,
        "stage_reference_quantity" => 2,
        "stage_checks"             => 1,
        "auto_acceptance"          => 0
    ];

    public static function create(array $attributes = [])
    {
        (new Facility)->guardAgainstDuplicateFacilityName(array_get($attributes, "name", ""));

        return parent::create($attributes);
    }

    public static function scopeTrainingRequired($query)
    {
        return $query->where("training_required", "=", 1);
    }

    public function applications()
    {
        return $this->hasMany(\App\Modules\Visittransfer\Models\Application::class);
    }

    public function removeTrainingSpace()
    {
        if ($this->training_required == 1) {
            $this->decrement("training_spaces");
        }
    }

    private function guardAgainstDuplicateFacilityName($proposedName)
    {
        if (Facility::where("name", "LIKE", $proposedName)->count() > 0) {
            throw new DuplicateFacilityNameException($proposedName);
        }
    }
}