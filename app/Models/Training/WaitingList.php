<?php

namespace App\Models\Training;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaitingList extends Model
{
    use SoftDeletes;

    public $table = 'training_waiting_list';

    protected $dates = ['deleted_at'];

    const ATC_DEPARTMENT = 1;
    const PILOT_DEPARTMENT = 2;

    public function getRouteKeyName()
    {
        return 'slug';
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
            'list_id')->using(WaitingListAccount::class)->withPivot(['id', 'position', 'deleted_at'])->withTimestamps();
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
     * @return void
     */
    public function removeFromWaitingList(Account $account)
    {
        $base = $this->accounts()->where('account_id', $account->id)->first()->pivot;
        $position = $base->position;

        $this->accounts->transform(function ($item, $key) use ($position) {
            if ($item->pivot->position > $position) {
                return $item->pivot->decrementPosition();
            }
        });

        // soft delete WaitingListUser and reset the position to -1
        $base->update(['deleted_at' => now(), 'position' => -1]);
    }

    /**
     * Promote an Accounts' position within a WaitingList by the number passed.
     *
     * @param Account $account
     * @param int $position
     */
    public function promote(Account $account, $position = 1)
    {
        if (!$this->accounts->contains($account)) {
            throw new ModelNotFoundException($this);
        }

        $entry = $this->accounts()->where('account_id', $account->id)->first();

        // current position before promotion
        $oldPosition = $entry->pivot->position;

        // deals with the actual promoted record; returns that new position
        $newPosition = $entry->pivot->decrementPosition($position);

        $this->accounts->transform(function ($item, $key) use ($oldPosition, $newPosition) {
            if ($item->pivot->position < $oldPosition) {
                return $item->pivot->incrementPosition();
            }
        });
    }

    /**
     * Demote an Accounts' position within a WaitingList by the number passed.
     *
     * @param Account $account
     * @param int $position
     */
    public function demote(Account $account, $position = 1)
    {
        if (!$this->accounts->contains($account)) {
            throw new ModelNotFoundException($this);
        }

        $entry = $this->accounts()->where('account_id', $account->id)->first();

        $oldPosition = $entry->pivot->position;

        $newPosition = $entry->pivot->incrementPosition($position);

        $this->accounts->transform(function ($item, $key) use ($oldPosition, $newPosition) {
            if ($item->pivot->position > $oldPosition) {
                return $item->pivot->decrementPosition();
            }
        });
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
     * Remove a Manager from a WaitingList
     * @param $account
     */
    public function removeManager($account)
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

    public function isAtcList()
    {
        return $this->department == self::ATC_DEPARTMENT;
    }

    public function isPilotList()
    {
        return $this->department == self::PILOT_DEPARTMENT;
    }

    public function __toString()
    {
        return $this->name;
    }
}
