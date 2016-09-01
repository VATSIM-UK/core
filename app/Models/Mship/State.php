<?php

namespace App\Models\Mship;

use App\Models\Mship\Account;
use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use Carbon\Carbon;

class State extends \Eloquent
{
    use RecordsActivity;
    protected $table      = "mship_state";
    protected $primaryKey = "id";
    protected $dates      = ['created_at', 'deleted_at'];
    protected $hidden     = ['id'];

    public static function findByCode($code)
    {
        return State::hasCode($code)->first();
    }

    public function scopeOfType($query, $type)
    {
        return $query->whereType($type);
    }

    public function scopeHasCode($query, $code)
    {
        return $query->whereCode($code);
    }

    public function account()
    {
        return $this->belongsToMany(Account::class, "mship_account_state", "state_id", "account_id")
                    ->withPivot(["start_at", "end_at"]);
    }

    public function __toString()
    {
        return "[" . $this->code . "] " . $this->name;
    }
}
