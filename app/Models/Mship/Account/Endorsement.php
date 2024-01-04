<?php

namespace App\Models\Mship\Account;

use App\Models\Atc\PositionGroup;
use App\Models\Model;

class Endorsement extends Model
{
    protected $table = 'mship_account_endorsement';

    protected $with = ['positionGroup'];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function positionGroup()
    {
        return $this->belongsTo(PositionGroup::class);
    }
}
