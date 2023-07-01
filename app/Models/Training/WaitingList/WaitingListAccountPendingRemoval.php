<?php

namespace App\Models\Training\WaitingList;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaitingListAccountPendingRemoval extends Pivot
{
    use SoftDeletes;
    
    public $timestamps = true;

    public $table = 'training_waiting_list_account_pending_removal';

    public function waitingListAccount()
    {
        return $this->belongsTo(WaitingListAccount::class, 'waiting_list_account_id');
    }

    public function cancelRemoval()
    {
        $this->cancelled_at = Carbon::now();
        $this->save();
        $this->delete();
    }

    public function markComplete()
    {
        $this->delete();
    }

    public function markReminderSent()
    {
        $this->reminder_sent_at = Carbon::now();
        $this->save();
    }
}
