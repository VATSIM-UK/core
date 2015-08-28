<?php

namespace App\Models\Mship\Account;

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class Alias extends \Eloquent implements UserInterface, RemindableInterface
{

    use UserTrait, RemindableTrait, SoftDeletingTrait;

    protected $table      = "mship_account_alias";
    protected $primaryKey = "account_alias_id";
    protected $dates      = ['created_at', 'deleted_at'];
    protected $hidden     = ['account_alias_id'];
    protected $touches    = ['account'];
}
