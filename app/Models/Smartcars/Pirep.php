<?php

namespace App\Models\Smartcars;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Smartcars\Pirep
 *
 * @property int $id
 * @property int $bid_id
 * @property int $aircraft_id
 * @property string $route
 * @property string $flight_time
 * @property int $landing_rate
 * @property string $comments
 * @property float $fuel_used
 * @property string $log
 * @property int $status
 * @property bool $passed
 * @property string|null $pass_reason
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Smartcars\Bid $bid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Pirep belongsTo($cid)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Pirep whereAircraftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Pirep whereBidId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Pirep whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Pirep whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Pirep whereFlightTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Pirep whereFuelUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Pirep whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Pirep whereLandingRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Pirep whereLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Pirep wherePassReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Pirep wherePassed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Pirep whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Pirep whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Smartcars\Pirep whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Pirep extends Model
{
    protected $table = 'smartcars_pirep';
    protected $fillable = [
        'bid_id',
        'flight_id',
    ];
    protected $dates = [
        'deleted_at',
    ];
    protected $casts = [
        'passed' => 'boolean',
    ];

    public function bid()
    {
        return $this->hasOne(\App\Models\Smartcars\Bid::class, 'id', 'bid_id');
    }

    public function scopeBelongsTo($query, $cid)
    {
        return $query->whereHas('bid', function ($query) use ($cid) {
            $query->where('account_id', '=', $cid);
        });
    }

    public function markPassed($reason = 'Flight passed all criteria.')
    {
        $this->passed = true;
        $this->pass_reason = $reason;
    }

    public function markFailed($reason = 'Flight failed at least one of the required criteria.')
    {
        $this->passed = false;
        $this->pass_reason = $reason;
    }
}
