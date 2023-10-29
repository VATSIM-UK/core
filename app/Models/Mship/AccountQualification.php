<?php

namespace App\Models\Mship;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountQualification extends Pivot
{
    use SoftDeletes;

    protected $table = 'mship_account_qualification';

    protected $primaryKey = 'id';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = ['id'];

    public $incrementing = true;
}
