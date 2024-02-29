<?php

namespace App\Models\Atc;

use App\Models\Model;

class PositionGroupPosition extends Model
{
    public function positionGroup()
    {
        return $this->belongsTo(PositionGroup::class);
    }
}
