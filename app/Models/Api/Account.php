<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Api\Account
 *
 * @mixin \Eloquent
 */
class Account extends Model
{
    protected $table = 'api_account';
    public $fillable = ['name', 'api_token'];
}
