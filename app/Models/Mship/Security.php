<?php

namespace App\Models\Mship;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use Carbon\Carbon;

/**
 * App\Models\Mship\Security
 *
 * @property integer $security_id
 * @property string $name
 * @property integer $alpha
 * @property integer $numeric
 * @property integer $symbols
 * @property integer $length
 * @property integer $expiry
 * @property boolean $optional
 * @property boolean $default
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Security[] $accountSecurity
 */
class Security extends \Eloquent {

	use SoftDeletingTrait, RecordsActivity;
        protected $table = "mship_security";
        protected $primaryKey = "security_id";
        protected $dates = ['created_at', 'deleted_at'];
        protected $hidden = ['security_id'];

        public function accountSecurity(){
            return $this->hasMany("\App\Models\Mship\Account\Security", "security_id", "security_id");
        }

        public static function getDefault(){
            return Security::where("default", "=", 1)->first();
        }
}
