<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Relations\Pivot;

class WaitingListAccountStatus extends Pivot
{
    public $table = 'training_waiting_list_account_status';

    protected $dates = ['start_at', 'end_at'];
}
