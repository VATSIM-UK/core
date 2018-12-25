<?php

namespace App\Models\Mship;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AccountQualification extends Pivot
{
    use SoftDeletes;

    protected $table = 'mship_account_qualification';
    protected $primaryKey = 'id';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['id'];
}
