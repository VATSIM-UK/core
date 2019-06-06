<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Relations\Pivot;

class WaitingListAccountFlag extends Pivot
{
    protected $guarded = [];
    protected $dates = ['marked_at'];
    protected $table = 'training_waiting_list_account_flag';
    protected $appends = ['value'];
    public $timestamps = false;
    protected $primaryKey  = 'id';

    public function mark()
    {
        $this->marked_at = now();
        $this->save();
    }

    public function unMark()
    {
        $this->marked_at = null;
        $this->save();
    }

    public function getValueAttribute()
    {
        return !is_null($this->marked_at);
    }
}
