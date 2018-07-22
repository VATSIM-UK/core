<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class WaitingListStatus extends Model
{
    use SoftDeletes;

    public $table = 'training_waiting_list_status';

    protected $casts = [
        'default' => 'boolean',
    ];



    public function waitingListAccount()
    {
        return $this->hasMany(WaitingListAccount::class, 'status_id');
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
