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
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Security whereSecurityId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Security whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Security whereAlpha($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Security whereNumeric($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Security whereSymbols($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Security whereLength($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Security whereExpiry($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Security whereOptional($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Security whereDefault($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Security whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Security whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Security whereDeletedAt($value)
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
