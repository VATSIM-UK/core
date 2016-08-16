<?php

namespace App\Modules\Visittransfer\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Ais\Models\Fir
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Aerodrome[]  $airfields
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Fir\Sector[] $sectors
 */
class Reference extends Model
{

    protected $table      = "vt_reference";
    protected $fillable   = [
        "application_id",
        "account_id",
        "email",
        "relationship",
    ];
    protected $touches    = ["application"];
    public    $timestamps = false;

    const STATUS_DRAFT              = 10;
    const STATUS_REQUESTED          = 30;
    const STATUS_UNDER_REVIEW       = 50;
    const STATUS_ACCEPTED           = 90;
    const STATUS_REJECTED           = 95;

    static $REFERENCE_IS_SUBMITTED = [
        self::STATUS_UNDER_REVIEW,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
    ];

    public static function scopePending($query)
    {
        return $query->whereNull("submitted_at");
    }

    public static function scopeStatus($query, $status)
    {
        return $query->where("status", "=", $status);
    }

    public static function scopeStatusIn($query, Array $stati)
    {
        return $query->whereIn("status", $stati);
    }

    public static function scopeDraft($query){
        return $query->status(self::STATUS_DRAFT);
    }

    public static function scopeRequested($query)
    {
        return $query->status(self::STATUS_REQUESTED);
    }

    public static function scopeSubmitted($query)
    {
        return $query->statusIn(self::$REFERENCE_IS_SUBMITTED);
    }

    public static function scopeUnderReview($query)
    {
        return $query->status(self::STATUS_UNDER_REVIEW);
    }

    public static function scopeAccepted($query){
        return $query->status(self::STATUS_ACCEPTED);
    }

    public static function scopeRejected($query){
        return $query->status(self::STATUS_REJECTED);
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class);
    }

    public function application()
    {
        return $this->belongsTo(\App\Modules\Visittransfer\Models\Application::class);
    }
}