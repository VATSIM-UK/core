<?php

namespace Models\Sso;

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Account extends \Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait, SoftDeletingTrait;
        protected $table = "sso_account";
        protected $primaryKey = "sso_account_id";
        protected $dates = ['created_at', 'updated_at', 'deleted_at'];
        protected $hidden = ['account_id'];

        public function tokens(){
            return $this->hasMany("\Models\Sso\Token", "sso_account_id", "sso_account_id");
        }
}
