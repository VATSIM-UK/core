<?php

namespace App\Models\Mship;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountState extends Pivot
{
    protected $table = 'mship_account_state';

    protected $casts = [
        'start_at' => 'datetime',
    ];

    protected $hidden = ['id'];
}
