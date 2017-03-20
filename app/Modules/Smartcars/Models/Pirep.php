<?php

namespace App\Modules\Smartcars\Models;

use Illuminate\Database\Eloquent\Model;

class Pirep extends Model
{
    protected $table      = 'smartcars_pirep';
    protected $fillable   = [
        'bid_id',
        'flight_id',
    ];
    public $timestamps    = true;
    protected $dates      = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function bid()
    {
        return $this->hasOne(\App\Modules\Smartcars\Models\Bid::class, 'id', 'bid_id');
    }

    public function scopeBelongsTo($query, $cid)
    {
        return $query->whereHas('bid', function ($query) use ($cid) {
            $query->where('account_id', '=', $cid);
        });
    }
}
