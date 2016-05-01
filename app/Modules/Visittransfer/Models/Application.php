<?php

namespace App\Modules\Visittransfer\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Ais\Models\Fir
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Aerodrome[] $airfields
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Fir\Sector[] $sectors
 */
class Application extends Model {

    protected $table = "vt_application";
    protected $fillable = [
        "icao", "name",
    ];

    public static $TYPE_VISIT = 10;
    public static $TYPE_TRANSFER = 40;

    public static $STATUS_IN_PROGRESS = 10;
    public static $STATUS_ACCEPTED = 90;
    public static $STATUS_COMPLETE = 95;
    public static $STATUS_LAPSED = 97;
    public static $STATUS_CANCELLED = 99;

    public static function scopeOfType($query, $type){
        return $query->where("type", "=", $type);
    }

    public static function scopeVisit($query){
        return $query->ofType(self::$TYPE_VISIT);
    }

    public static function scopeTransfer($query){
        return $query->ofType(self::$TYPE_TRANSFER);
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class);
    }

    public function skippedStages()
    {
        return $this->hasMany(StageSkip::class, 'application_id', 'id');
    }

    public static function stageUnfinished($status)
    {
        return $status === self::STAGE_PENDING || $status === self::STAGE_INCOMPLETE;
    }

    public static function stageFinished($status)
    {
        return $status === self::STAGE_COMPLETE || $status === self::STAGE_SKIPPED;
    }

    public function stageSkipped($stage_key)
    {
        $this->skippedStages->load('stage');

        return !$this->skippedStages->filter(function ($skip) use ($stage_key) {
            return $skip->stage->key === $stage_key;
        })->isEmpty();
    }

    public function termsStatus()
    {
        return self::STAGE_COMPLETE;
    }

    public function typeStatus()
    {
        if ($this->type_id === null) {
            return self::STAGE_INCOMPLETE;
        } else {
            return self::STAGE_COMPLETE;
        }
    }

    public function facilityStatus()
    {
        if ($this->stageSkipped('FACILITY')) {
            return self::STAGE_SKIPPED;
        } elseif ($this->stageUnfinished($this->typeStatus())) {
            return self::STAGE_PENDING;
        } elseif ($this->facility_id === null) {
            return self::STAGE_INCOMPLETE;
        } else {
            return self::STAGE_COMPLETE;
        }
    }

    public function statementStatus()
    {
        if ($this->stageSkipped('STATEMENT')) {
            return self::STAGE_SKIPPED;
        } elseif ($this->stageUnfinished($this->facilityStatus())) {
            return self::STAGE_PENDING;
        } elseif (!$this->submitted_statement) {
            return self::STAGE_INCOMPLETE;
        } else {
            return self::STAGE_COMPLETE;
        }
    }

    public function addRefereeStatus()
    {
        if ($this->stageSkipped('ADD_REFEREES')) {
            return self::STAGE_SKIPPED;
        } elseif ($this->stageUnfinished($this->statementStatus())) {
            return self::STAGE_PENDING;
        } elseif (!$this->submitted_referees) {
            return self::STAGE_INCOMPLETE;
        } else {
            return self::STAGE_COMPLETE;
        }
    }

    public function submitStatus()
    {
        if ($this->submitted_application) {
            return self::STAGE_COMPLETE;
        } elseif ($this->stageFinished($this->addRefereeStatus())) {
            return self::STAGE_INCOMPLETE;
        } else {
            return self::STAGE_PENDING;
        }
    }

    public function checksStatus()
    {
        //
    }

    public function refreviewStatus()
    {
        //
    }

    public function refsubmitStatus()
    {
        //
    }

    public function refdecisionStatus()
    {
        //
    }

    public function reviewStatus()
    {
        //
    }

    public function outcomeStatus()
    {
        //
    }
}