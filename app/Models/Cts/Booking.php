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

    protected $hidden = [
        'type_id', 'groupID', 'local_id', 'eurobook_id', 'eurobook_import', 'member_id', 'time_booked',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }

    public function scopeNetworkAtc($query)
    {
        return $query->where(function ($subQuery) {
            return $subQuery->where('position', 'LIKE', '%_DEL')
                ->orWhere('position', 'LIKE', '%_GND')
                ->orWhere('position', 'LIKE', '%_TWR')
                ->orWhere('position', 'LIKE', '%_APP')
                ->orWhere('position', 'LIKE', '%_CTR')
                ->orWhere('position', 'LIKE', '%_FSS');
        });
    }

    public function scopeNotEvent($query)
    {
        return $query->where('type', '!=', 'EV');
    }

    public function isEvent()
    {
        return $this->type == 'EV';
    }

    public function isExam()
    {
        return $this->type == 'EX';
    }

    public function isMemberBooking()
    {
        return $this->type == 'BK';
    }

    public function isMentoring()
    {
        return $this->type == 'ME';
    }
}
