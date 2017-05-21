<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class BasicUser extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $table = 'basic_users';
    public $timestamps = false;
}
