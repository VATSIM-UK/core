<?php

namespace App\Models\Sso;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Sso\Token
 *
 * @property integer $sso_token_id
 * @property string $token
 * @property integer $sso_account_id
 * @property string $return_url
 * @property integer $account_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $expires_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read mixed $is_expired
 * @property-read mixed $display_value
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Timeline\Entry[] $timelineEntriesOwner
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Timeline\Entry[] $timelineEntriesExtra
 * @property-read mixed $timeline_entries_recent
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Token tokenValue($tokenValue)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Token valid()
 */
class Token extends \App\Models\aModel {

	use SoftDeletingTrait;
        protected $table = "sso_token";
        protected $primaryKey = "sso_token_id";
        protected $dates = ['created_at', 'updated_at', 'deleted_at'];
        protected $hidden = ['token_id'];

        public function account(){
            return $this->belongsTo("\App\Models\Mship\Account", "account_id", "account_id");
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
