<?php

namespace App\Models\Smartcars;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class Bid extends Model
{
    use SoftDeletingTrait;

    protected $table      = 'smartcars_bid';
    protected $fillable   = [
        'flight_id',
        'account_id',
    ];
    public $timestamps    = true;
    protected $dates      = [
        'created_at',
        'updated_at',
        'completed_at',
        'deleted_at',
    ];

    public function flight()
    {
        return $this->hasOne(\App\Models\Smartcars\Flight::class, 'id', 'flight_id');
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, 'account_id', 'id');
    }

    public function scopeFlightId($query, $flightId)
    {
        return $query->where('flight_id', '=', $flightId);
    }

    public function scopeAccountId($query, $accountId)
    {
        return $query->where('account_id', '=', $accountId);
    }

    public function scopePending($query)
    {
        return $query->whereNull('completed_at');
    }

    public function complete()
    {
        $this->completed_at = \Carbon\Carbon::now();
        $this->save();
    }
}
