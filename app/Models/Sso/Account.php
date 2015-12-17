<?php

namespace App\Models\Sso;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class Account extends \App\Models\aTimelineEntry {

	use SoftDeletingTrait;
        protected $table = "sso_account";
        protected $primaryKey = "sso_account_id";
        protected $dates = ['created_at', 'updated_at', 'deleted_at'];
        protected $hidden = ['account_id'];

        public function tokens(){
            return $this->hasMany("\App\Models\Sso\Token", "sso_account_id", "sso_account_id");
        }

    public function getDisplayValueAttribute() {
        return "NOT YET DEFINED IN __ACCOUNT__ MODELS";
    }

}
