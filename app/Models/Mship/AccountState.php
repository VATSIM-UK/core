<?php

namespace App\Models\Mship;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountState extends Pivot
{
    protected $table = 'mship_account_state';

    protected $dates = ['start_at', 'end_at'];

    protected $hidden = ['id'];
}
