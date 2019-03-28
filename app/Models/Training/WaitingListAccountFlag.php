<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Model;

class WaitingListAccountFlag extends Model
{
    protected $guarded = [];
    protected $dates = ['marked_at'];

    public function getValueAttribute()
    {
        return !is_null($this->marked_at);
    }
}
