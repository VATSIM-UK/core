<?php

namespace Models\Mship\Account;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Qualification extends \Eloquent {

	use SoftDeletingTrait;
        protected $table = "mship_account_qualification";
        protected $primaryKey = "account_qualification_id";
        protected $dates = ['created_at', 'updated_at', 'deleted_at'];
        protected $hidden = ['account_qualification_id'];

        public function account(){
            return $this->belongsTo("\Models\Mship\Account\Account", "account_id", "account_id");
        }

        public function qualification(){
            return $this->belongsTo("\Models\Mship\Qualification", "qualification_id", "qualification_id");
        }

        public function __toString(){
            return isset($this->attributes['name_long']) ? $this->attributes['name_long'] : "";
        }
}
