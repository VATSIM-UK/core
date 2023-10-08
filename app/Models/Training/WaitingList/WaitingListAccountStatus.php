<?php

namespace App\Models\Training\WaitingList;

use Illuminate\Database\Eloquent\Relations\Pivot;

class WaitingListAccountStatus extends Pivot
{
    public $timestamps = false;

    public $table = 'training_waiting_list_account_status';

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function isActive()
    {
        return is_null($this->end_at);
    }

    public function endStatus()
    {
        $this->end_at = now();

        $this->save();
    }

    public function scopeActiveStatus($query)
    {
        return $query->whereNull('deleted_at')->first();
    }
}
