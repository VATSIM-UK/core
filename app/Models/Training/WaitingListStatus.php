<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Training\WaitingListAccount;

class WaitingListStatus extends Model
{
    use SoftDeletes;

    public $table = 'training_waiting_list_status';

    protected $casts = [
        'default' => 'boolean',
    ];

    public function waitingListAccount()
    {
        return $this->belongsToMany(WaitingListAccount::class, 'training_waiting_list_account_status',
            'id', 'status_id')->using(WaitingListAccountStatus::class);
    }

    /**
     * Get the default state.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('default', true)->first();
    }
}
