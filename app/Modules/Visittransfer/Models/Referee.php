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
        "email", "relationship",
    ];
    protected $touches = ["application"];
    public $timestamps = false;

    const STATUS_DRAFT = 10;
    const STATUS_REQUESTED = 30;
    const STATUS_COMPLETED = 35;
    const STATUS_UNDER_REVIEW = 50;
    const STATUS_ACCEPTED = 90;
    const STATUS_REJECTED = 95;

    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class);
    }

    public function application()
    {
        return $this->belongsTo(\App\Modules\Vt\Models\Application::class);
    }
}