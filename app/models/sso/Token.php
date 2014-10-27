<?php

namespace Models\Sso;

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Token extends \Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait, SoftDeletingTrait;
        protected $table = "sso_token";
        protected $primaryKey = "sso_token_id";
        protected $dates = ['created_at', 'updated_at', 'deleted_at'];
        protected $hidden = ['token_id'];

        public function account(){
            return $this->belongsTo("\Models\Mship\Account\Account", "account_id", "account_id");
        }

        public function getIsExpiredAttribute(){
            return Carbon::createFromFormat("Y-m-d H:i:s", $this->expires)->diffInSeconds() > 0;
        }
}
