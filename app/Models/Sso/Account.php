<?php

namespace App\Models\Sso;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Sso\Account
 *
 * @property integer $sso_account_id
 * @property string $username
 * @property string $name
 * @property string $api_key_public
 * @property string $api_key_private
 * @property string $salt
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sso\Token[] $tokens
 * @property-read mixed $display_value
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Timeline\Entry[] $timelineEntriesOwner
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Timeline\Entry[] $timelineEntriesExtra
 * @property-read mixed $timeline_entries_recent
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Account whereSsoAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Account whereUsername($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Account whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Account whereApiKeyPublic($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Account whereApiKeyPrivate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Account whereSalt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Account whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Account whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sso\Account whereDeletedAt($value)
 * @mixin \Eloquent
 */
class Account extends \App\Models\aModel {

	use SoftDeletingTrait, RecordsActivity;

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
