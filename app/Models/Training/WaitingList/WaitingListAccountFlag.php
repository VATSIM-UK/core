<?php

namespace App\Models\Training\WaitingList;

use App\Events\Training\AccountManualFlagChanged;
use Illuminate\Database\Eloquent\Relations\Pivot;

class WaitingListAccountFlag extends Pivot
{
    protected $guarded = [];

    protected $casts = ['marked_at' => 'datetime'];

    protected $table = 'training_waiting_list_account_flag';

    protected $appends = ['value'];

    public $timestamps = false;

    protected $primaryKey = 'id';

    public function mark()
    {
        $this->marked_at = now();
        $this->save();

        event(new AccountManualFlagChanged($this->waitingListAccount->account, $this->waitingListAccount->waitingList));
    }

    public function unMark()
    {
        $this->marked_at = null;
        $this->save();

        event(new AccountManualFlagChanged($this->waitingListAccount->account, $this->waitingListAccount->waitingList));
    }

    public function getValueAttribute()
    {
        if ($this->flag->endorsement_id) {
            return $this->flag->endorsement->conditionsMetForUser($this->waitingListAccount->account);
        }

        return ! is_null($this->marked_at);
    }

    public function waitingListAccount()
    {
        return $this->belongsTo(WaitingListAccount::class)->withTrashed();
    }

    public function flag()
    {
        return $this->belongsTo(WaitingListFlag::class);
    }
}
