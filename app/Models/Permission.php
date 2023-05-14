<?php

namespace App\Models;

class Permission extends \Spatie\Permission\Models\Permission
{
    protected $guarded = [];

    protected $fillable = ['name', 'guard_name'];
}
