<?php

namespace Models\Sso;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Account extends \Models\aTimelineEntry {

	use SoftDeletingTrait;
        protected $table = "sso_account";
        protected $primaryKey = "sso_account_id";
        protected $dates = ['created_at', 'updated_at', 'deleted_at'];
        protected $hidden = ['account_id'];

        public function tokens(){
            return $this->hasMany("\Models\Sso\Token", "sso_account_id", "sso_account_id");
        }

    public function getDisplayValueAttribute() {
        return "NOT YET DEFINED IN __ACCOUNT__ MODELS";
    }

}
