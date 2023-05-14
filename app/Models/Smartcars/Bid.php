<?php

namespace App\Models\Smartcars;

use App\Events\Smartcars\BidCompleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Smartcars\Bid.
 *
 * @property int $id
 * @property int $flight_id
 * @property int $account_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $completed_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read \App\Models\Smartcars\Flight $flight
 * @property-read \App\Models\Smartcars\Pirep $pirep
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Smartcars\Posrep[] $posreps
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Bid accountId($accountId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Bid flightId($flightId)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Smartcars\Bid onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Bid pending()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Bid whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Bid whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Bid whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Bid whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Bid whereFlightId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Bid whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Bid whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Smartcars\Bid withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Smartcars\Bid withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Bid extends Model
{
    use SoftDeletingTrait;

    protected $table = 'smartcars_bid';

    protected $fillable = [
        'flight_id',
        'account_id',
    ];

    protected $dates = [
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

        event(new BidCompleted($this));
    }

    public function pirep()
    {
        return $this->hasOne(Pirep::class, 'bid_id', 'id');
    }

    public function posreps()
    {
        return $this->hasMany(Posrep::class, 'bid_id', 'id');
    }
}
