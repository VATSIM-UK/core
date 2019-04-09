<?php

namespace App\Models\Training;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Model;

class WaitingListFlag extends Model
{
    protected $guarded = [];
    protected $table = 'training_waiting_list_flags';

    protected static function boot()
    {
        parent::boot();

        self::deleting(function (WaitingListFlag $flag) {
            $flag->waitingListAccounts()->detach();
        });
    }

    public function waitingListAccounts()
    {
        return $this->belongsToMany(
            WaitingListAccount::class,
            'training_waiting_list_account_flag',
            'flag_id',
            'waiting_list_account_id'
        )->withPivot(['marked_at'])->using(WaitingListAccountFlag::class);
    }
}
