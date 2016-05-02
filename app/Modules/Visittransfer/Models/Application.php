<?php

namespace App\Modules\Visittransfer\Models;

use App\Modules\Vt\Events\ApplicationCreated;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Ais\Models\Fir
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Aerodrome[]  $airfields
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Fir\Sector[] $sectors
 */
class Application extends Model
{

    protected $table    = "vt_application";
    protected $fillable = [
        "type",
        "account_id",
        "facility_id",
        "statement",
        "status",
    ];

    const TYPE_VISIT    = 10;
    const TYPE_TRANSFER = 40;

    const STATUS_IN_PROGRESS  = 10; // Member hasn't yet submitted application formally.
    const STATUS_SUBMITTED    = 30; // Member has formally submitted application.
    const STATUS_UNDER_REVIEW = 50; // References and checks have been completed.
    const STATUS_ACCEPTED     = 60; // Application has been accepted by staff
    const STATUS_COMPLETED    = 90; // Application has been formally completed, visit/transfer complete.
    const STATUS_LAPSED       = 97; // Application has lapsed.
    const STATUS_CANCELLED    = 98; // Application has been cancelled
    const STATUS_REJECTED     = 99; // Application has been rejected by staff

    public static function scopeOfType($query, $type)
    {
        return $query->where("type", "=", $type);
    }

    public static function scopeVisit($query)
    {
        return $query->ofType(self::TYPE_VISIT);
    }

    public static function scopeTransfer($query)
    {
        return $query->ofType(self::TYPE_TRANSFER);
    }

    public static function scopeStatus($query, $status)
    {
        return $query->whereStatus($status);
    }

    public static function scopeNotStatus($query, $status)
    {
        return $query->whereNotStatus($status);
    }

    public static function scopeStatusIn($query, Array $stati)
    {
        return $query->whereIn("status", $stati);
    }

    public static function scopeStatusNotIn($query, Array $stati)
    {
        return $query->whereNotIn("status", $stati);
    }

    public static function scopeOpen($query)
    {
        return $query->statusIn([
            self::STATUS_IN_PROGRESS,
            self::STATUS_SUBMITTED,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_ACCEPTED
        ]);
    }

    public static function scopeClosed($query)
    {
        return $query->status([
            self::STATUS_CANCELLED,
            self::STATUS_REJECTED,
            self::STATUS_LAPSED,
            self::STATUS_COMPLETED
        ]);
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class);
    }

    public function facility(){
        return $this->belongsTo(\App\Modules\Visittransfer\Models\Facility::class);
    }

    public function referees(){
        return $this->hasMany(\App\Modules\Visittransfer\Models\Referee::class);
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

    public function getTypeStringAttribute(){
        if($this->is_visit){
            return "Visit";
        }

        return "Transfer";
    }

    public function getIsVisitAttribute(){
        return $this->type == self::TYPE_VISIT;
    }

    public function getIsTransferAttribute(){
        return $this->type == self::TYPE_TRANSFER;
    }
}