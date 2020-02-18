<?php

namespace App\Models\Training;

use App\Events\Training\WaitingListCreated;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Models\Training\WaitingList\WaitingListFlag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaitingList extends Model
{
    use SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        self::created(function ($model) {
            event(new WaitingListCreated($model));
        });
    }

    public $table = 'training_waiting_list';

    protected $dates = ['deleted_at'];

    const ATC_DEPARTMENT = 'atc';
    const PILOT_DEPARTMENT = 'pilot';

    /**
     * A Waiting List can be managed by many Staff Members (Accounts)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function staff()
    {
        return $this->belongsToMany(
            Account::class,
            'training_waiting_list_staff',
            'list_id',
            'account_id'
        )->withTimestamps();
    }

    /**
     * Many WaitingLists can have many Accounts (pivot).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function accounts()
    {
        return $this->belongsToMany(
            Account::class,
            'training_waiting_list_account',
            'list_id'
        )->using(WaitingListAccount::class)
            ->withPivot([
                'id',
                'deleted_at',
                'notes',
                'created_at'
            ])->wherePivot('deleted_at', null);
    }

    /**
     * One WaitingList can have many flags associated with it.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function flags()
    {
        return $this->hasMany(WaitingListFlag::class, 'list_id');
    }

    /**
     * Associate a flag with a waiting list.
     *
     * @param  WaitingListFlag  $flag
     * @return mixed
     */
    public function addFlag(WaitingListFlag $flag)
    {
        $savedFlag = $this->flags()->save($flag);

        $this->accounts()->each(function ($account) use ($flag) {
            $account->pivot->flags()->attach($flag);
        });

        return $savedFlag;
    }

    /**
     * Remove a flag from a waiting list.
     *
     * @param  WaitingListFlag  $flag
     * @return mixed
     */
    public function removeFlag(WaitingListFlag $flag)
    {
        return $this->flags()->delete($flag);
    }

    /**
     * Add an Account to a waiting list.
     *
     * @param  Account  $account
     * @param  Account  $staffAccount
     * @param  Carbon|null  $createdAt
     */
    public function addToWaitingList(Account $account, Account $staffAccount, Carbon $createdAt = null)
    {
        $timestamp = $createdAt != null ? $createdAt : Carbon::now();
        $this->accounts()->attach($account, ['added_by' => $staffAccount->id,]);

        // the following code is required as the timestamp for created_at gets overridden during the creation
        // process, despite being disabled on the pivot!!
        $pivot = $this->accounts()->find($account->id)->pivot;
        $pivot->created_at = $timestamp;
        $pivot->save();
    }

    /**
     * Remove an Account from a waiting list.
     *
     * @param  Account  $account
     * @return void
     */
    public function removeFromWaitingList(Account $account)
    {
        $waitingListAccount = $this->accounts()->where('account_id', $account->id)->first()->pivot;
        $waitingListAccount->delete();
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
        return (string) $this->name;
    }
}
