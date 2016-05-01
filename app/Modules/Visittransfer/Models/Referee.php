<?php

namespace App\Modules\Visittransfer\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Modules\Ais\Models\Fir
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Aerodrome[] $airfields
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Modules\Ais\Models\Fir\Sector[] $sectors
 */
class Referee extends Model {

    protected $table = "vt_referee";
    protected $fillable = [
        "application_id", "account_id",
    ];
    protected $touches = ["application"];
    public $timestamps = false;

    public static $STATUS_DRAFT = 10;
    public static $STATUS_REQUESTED = 30;
    public static $STATUS_COMPLETED = 35;
    public static $STATUS_UNDER_REVIEW = 50;
    public static $STATUS_ACCEPTED = 90;
    public static $STATUS_REJECTED = 95;

    public function account()
    {
        return $this->hasOne(\App\Models\Mship\Account::class);
    }

    public function application()
    {
        return $this->belongsTo(\App\Modules\Vt\Models\Application::class);
    }
}