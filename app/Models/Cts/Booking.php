<?php

namespace App\Models\Cts;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $connection = 'cts';
    protected $guarded = [];
    public const CREATED_AT = 'time_booked';
    public const UPDATED_AT = null;
    protected $attributes = ['local_id' => 0];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }
}
