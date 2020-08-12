<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends \Spatie\Permission\Models\Permission
{
    protected $guarded = [];
    protected $fillable = ['name', 'guard_name'];
}
