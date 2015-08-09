<?php

namespace Models\Sso;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class Token extends \Models\aTimelineEntry {

	use SoftDeletingTrait;
        protected $table = "sso_token";
        protected $primaryKey = "sso_token_id";
        protected $dates = ['created_at', 'updated_at', 'deleted_at'];
        protected $hidden = ['token_id'];

        public function account(){
            return $this->belongsTo("\Models\Mship\Account", "account_id", "account_id");
        }

        public function getIsExpiredAttribute(){
            return \Carbon\Carbon::createFromFormat("Y-m-d H:i:s", $this->expires)->diffInSeconds() > 0;
        }

        public function scopeTokenValue($query, $tokenValue){
            return $query->whereToken($tokenValue);
        }

        public function scopeValid($query){
            return $query->where("expires_at", ">=", \Carbon\Carbon::now()->toDateTimeString());
        }

    public function getDisplayValueAttribute() {
        return "NOT YET DEFINED IN __TOKEN__ MODEL";
    }

}
