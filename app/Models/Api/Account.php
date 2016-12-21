<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = "api_account";
    public $fillable = ['name', 'api_token'];
}
