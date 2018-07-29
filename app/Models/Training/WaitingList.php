<?php

namespace App\Models\Training;

use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Mship\Account;

class WaitingList extends Model
{
    use SoftDeletes, PivotEventTrait;

    public $table = 'training_waiting_list';

    protected $dates = ['deleted_at'];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function accountsStatus()
    {
        return $this->hasManyThrough(WaitingListAccount::class, WaitingListAccountStatus::class, 'status_id');
    }

    /**
     * A Waiting List can be managed by many Staff Members (Accounts)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function staff()
    {
        return $this->belongsToMany(Account::class, 'training_waiting_list_staff', 'list_id',
            'account_id')->withTimestamps();
    }

    /**
     * Many WaitingLists can have many Accounts (pivot).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'training_waiting_list_account',
            'list_id')->using(WaitingListAccount::class)->withPivot(['position'])->withTimestamps();
    }

    /**
     * Add an Account to a waiting list.
     *
     * @param Account $account | Collection
     * @param Account $staffAccount
     */
    public function addToWaitingList($account, Account $staffAccount)
    {
        $this->accounts()->attach($account, ['position' => $this->nextPosition(), 'added_by' => $staffAccount->id]);
    }

    /**
     * Remove an Account from a waiting list.
     *
     * @param Account $account
     * @return int
     */
    public function removeFromWaitingList(Account $account)
    {
        $position = $this->accounts()->where('account_id', $account->id)->first()->pivot->position;

        $this->accounts->transform(function ($item, $key) use ($position) {
            if ($item->pivot->position > $position) {
                return $item->pivot->decrementPosition();
            }
        });

        $this->accounts()->detach($account);
    }

    /**
     * Add a Manager to a WaitingList
     *
     * @param Account $account | Collection
     */
    public function addManager($account)
    {
        return $this->staff()->attach($account);
    }

    /**
     * Get the next position in the waiting list.
     *
     * @return int
     */
    private function nextPosition()
    {
        $size = $this->accounts()->count();

        return $size + 1;
    }

    /**
     * Retrieve all staff assigned to a waiting list.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStaff($query)
    {
        return $query->whereHas('staff');
    }

    public function __toString()
    {
        return $this->name;
    }
}
