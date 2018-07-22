<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaitingListAccount extends Pivot
{
    use SoftDeletes;

    public $table = 'training_waiting_list_account';

    protected static function boot()
    {
        parent::boot();

        self::created([get_called_class(), 'assignStatus']);
    }

    public function assignStatus()
    {
        $status = $this->status()->default();

        $this->attributes['status_id'] = $status->id;
    }

    public function status()
    {
        return $this->belongsTo(WaitingListStatus::class);
    }
}
