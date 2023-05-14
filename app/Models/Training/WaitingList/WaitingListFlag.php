<?php

namespace App\Models\Training\WaitingList;

use App\Models\Atc\Endorsement;
use Illuminate\Database\Eloquent\Model;

class WaitingListFlag extends Model
{
    protected $guarded = [];

    protected $table = 'training_waiting_list_flags';

    protected static function boot()
    {
        parent::boot();

        self::deleting(function (self $flag) {
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

    public function endorsement()
    {
        return $this->belongsTo(Endorsement::class);
    }
}
