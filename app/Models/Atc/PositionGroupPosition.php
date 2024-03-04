<?php

namespace App\Models\Atc;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PositionGroupPosition extends Pivot
{
    public function positionGroup()
    {
        return $this->belongsTo(PositionGroup::class);
    }
}
