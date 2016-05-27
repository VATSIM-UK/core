<?php

namespace App\Models\Mship;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use Carbon\Carbon;

/**
 * App\Models\Mship\Security
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Security[] $accountSecurity
 * @mixin \Eloquent
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
