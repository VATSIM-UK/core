<?php

namespace App\Models\Training;

use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaitingListAccount extends Pivot
{
    use SoftDeletes, PivotEventTrait;

    public $table = 'training_waiting_list_account';

    public $fillable = ['position'];

    public function setPositionAttribute($value)
    {
        $this->attributes['position'] = (int) $value;
    }

    public function decrementPosition($value = 1)
    {
        $this->position -= $value;
        $this->save();

        return $this->position;
    }

    public function incrementPosition($value = 1)
    {
        $this->position += $value;
        $this->save();

        return $this->position;
    }
}
