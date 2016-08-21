<?php

namespace App\Models\Mship\Account;

use App\Models\Mship\Account;
use App\Models\Mship\Security as SecurityType;
use App\Traits\RecordsActivity;
use Carbon\Carbon;
use Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Mship\Account\Security
 *
 * @property integer $account_security_id
 * @property integer $account_id
 * @property integer $security_id
 * @property string $value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $expires_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read \App\Models\Mship\Security $security
 * @property-read mixed $is_active
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Security whereAccountSecurityId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Security whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Security whereSecurityId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Security whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Security whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Security whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Security whereExpiresAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\Security whereDeletedAt($value)
 * @mixin \Eloquent
 */
class Security extends Model
{
    use SoftDeletingTrait, RecordsActivity;

    protected $table      = 'mship_account_security';
    protected $primaryKey = 'account_security_id';
    protected $dates      = ['created_at', 'updated_at', 'expires_at', 'deleted_at'];
    protected $hidden     = ['account_security_id'];
    protected $touches    = ['account'];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function security()
    {
        return $this->belongsTo(SecurityType::class, 'security_id', 'security_id');
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = Hash::make($value);
    }

    public function verifyPassword($value)
    {
        if ($this->value == self::hash($value)) {
            $this->value = $value;
            $this->save();
        }

        return Hash::check($value, $this->value);
    }

    public function getIsActiveAttribute()
    {
        if ($this->expires_at == null) {
            return true;
        } elseif ($this->value == '') {
            return false;
        } else {
            return $this->expires_at->diffInDays() > 0;
        }
    }

    /**
     * @deprecated should only be used for checking old hashes
     * @param $value
     * @return string
     */
    private static function hash($value)
    {
        return sha1(sha1($value));
    }

    public static function generate($hashed = false)
    {
        $pw = str_random(8) . '8!-';
        $pw = str_shuffle($pw);

        return ($hashed ? Hash::make($pw) : $pw);
    }

    public function expire()
    {
        $this->expires_at = Carbon::now();
        $this->save();
    }

    public function save(Array $options = [])
    {
        // Set the expiry date!
        if ($this->expires_at == null) {
            $securityType = SecurityType::find($this->type);
            if ($securityType && $securityType->expiry > 0) {
                $this->attributes['expires_at'] = Carbon::now()->addDays($securityType->expiry);
            }
        }

        parent::save();
    }
}
