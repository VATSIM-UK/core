<?php

namespace App\Models\Training\WaitingList;

use Illuminate\Database\Eloquent\Relations\Pivot;

class WaitingListAccountPendingRemoval extends Pivot
{
    public $timestamps = true;

    public $table = 'training_waiting_list_account_pending_removal';

    public function waitingListAccount()
    {
        return $this->belongsTo(WaitingListAccount::class, 'waiting_list_account_id');
    }

    public function cancelRemoval()
    {
        $this->status = 'Cancelled';
        $this->save();
    }

    public function markComplete()
    {
        $this->status = 'Completed';
        $this->save();
    }

    public function incrementEmailCount()
    {
        $this->emails_sent = $this->emails_sent + 1;
        $this->save();
    }

    public function isPendingRemoval()
    {
        return $this->status == 'Pending';
    }

}