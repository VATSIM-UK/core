<?php

namespace App\Models\Training;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaitingList extends Model
{
    use SoftDeletes;

    public $table = 'training_waiting_list';

    protected $dates = ['deleted_at'];

    protected $casts = ['active' => 'boolean'];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Many WaitingLists can have many Accounts (pivot).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function students()
    {
        return $this->belongsToMany(Account::class, 'training_waiting_list_account',
            'list_id')->using(WaitingListAccount::class);
    }

    /**
     * Add an Account to a waiting list.
     *
     * @param Account $account
     */
    public function addToWaitingList(Account $account)
    {
        return $this->students()->attach($account);
    }

    /**
     * Remove an Account from a waiting list.
     *
     * @param Account $account
     * @return int
     */
    public function removeFromWaitingList(Account $account)
    {
        return $this->students()->detach($account);
    }

    /**
     * Retrieve all active waiting lists.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
