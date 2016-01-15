<?php

namespace App\Models\Mship\Account;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Mship\Account\Qualification
 *
 * @property integer $account_qualification_id
 * @property integer $account_id
 * @property integer $qualification_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read \App\Models\Mship\Qualification $qualification
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Qualification atc()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Qualification atcTraining()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Qualification pilot()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Qualification pilotTraining()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Qualification admin()
 */
class Qualification extends \Eloquent
{

    use SoftDeletingTrait;

    protected $table      = "mship_account_qualification";
    protected $primaryKey = "account_qualification_id";
    protected $dates      = ['created_at', 'updated_at', 'deleted_at'];
    protected $hidden     = ['account_qualification_id'];
    protected $touches    = ['account'];

    public function account()
    {
        return $this->belongsTo("\App\Models\Mship\Account", "account_id", "account_id");
    }

    public function qualification()
    {
        return $this->belongsTo("\App\Models\Mship\Qualification", "qualification_id", "qualification_id");
    }

    public function __toString()
    {
        return isset($this->qualification->name_long) ? $this->qualification->name_long : "Unknown";
    }

    public function scopeAtc($query)
    {
        return $query->whereHas("qualification",
            function ($q) {
                $q->where("type", "=", "atc");
            }
        );
    }

    public function scopeAtcTraining($query)
    {
        return $query->whereHas("qualification",
            function ($q) {
                $q->where("type", "=", "training_atc");
            }
        );
    }

    public function scopePilot($query)
    {
        return $query->whereHas("qualification",
            function ($q) {
                $q->where("type", "=", "pilot");
            }
        );
    }

    public function scopePilotTraining($query)
    {
        return $query->whereHas("qualification",
            function ($q) {
                $q->where("type", "=", "training_pilot");
            }
        );
    }

    public function scopeAdmin($query)
    {
        return $query->whereHas("qualification",
            function ($q) {
                $q->where("type", "=", "admin");
            }
        );
    }
}
