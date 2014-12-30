<?php

namespace Models\Sso;


use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Token extends \Models\aTimelineEntry {

	use SoftDeletingTrait;
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

    public function getDisplayValueAttribute() {
        return "NOT YET DEFINED IN __TOKEN__ MODEL";
    }

}
