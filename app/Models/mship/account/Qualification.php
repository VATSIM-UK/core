<?php

namespace Models\Mship\Account;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

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
        return $this->belongsTo("\Models\Mship\Account", "account_id", "account_id");
    }

    public function qualification()
    {
        return $this->belongsTo("\Models\Mship\Qualification", "qualification_id", "qualification_id");
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
