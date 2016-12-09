<?php

namespace App\Modules\Community\Models;

use App\Models\Model;

class Membership extends Model
{
    protected $table      = 'community_membership';
    protected $primaryKey = 'id';
    public $timestamps    = true;
    public $dates         = ['created_at', 'updated_at', 'deleted_at'];
    public $fillable      = [
        'group_id',
        'account_id',
    ];
}
