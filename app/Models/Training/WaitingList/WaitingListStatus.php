<?php

namespace App\Models\Training\WaitingList;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaitingListStatus extends Model
{
    use SoftDeletes;

    public $table = 'training_waiting_list_status';

    protected $casts = [
        'default' => 'boolean',
    ];

    const DEFAULT_STATUS = 1;

    const DEFERRED = 2;

    public function waitingListAccount()
    {
        return $this->belongsToMany(
            WaitingListAccount::class,
            'training_waiting_list_account_status',
            'id',
            'status_id'
        )->using(WaitingListAccountStatus::class);
    }

    /**
     * Get the default state.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('default', true)->first();
    }

    public function __toString()
    {
        return $this->name;
    }
}
