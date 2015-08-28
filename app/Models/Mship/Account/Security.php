<?php

namespace App\Models\Mship\Account;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use Carbon\Carbon;
use App\Models\Mship\Security as SecurityType;

class Security extends \Eloquent
{

    use SoftDeletingTrait;

    protected $table      = "mship_account_security";
    protected $primaryKey = "account_security_id";
    protected $dates      = ['created_at', 'expires_at', 'deleted_at'];
    protected $hidden     = ['account_security_id'];

    protected $touches = ['account'];

    public function account()
    {
        return $this->belongsTo("\App\Models\Mship\Account", "account_id", "account_id");
    }

    public function security()
    {
        return $this->belongsTo("\App\Models\Mship\Security", "security_id", "security_id");
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = self::hash($value);
    }

    public function verifyPassword($value)
    {
        return $this->value == self::hash($value);
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

    private static function hash($value)
    {
        return sha1(sha1($value));
    }

    public static function generate($hashed = false)
    {
        $pw = str_random(8) . "8!-";
        $pw = str_shuffle($pw);

        return ($hashed ? self::hash($pw) : $pw);
    }

    public function expire()
    {
        $this->expires_at = Carbon::now()->toDateTimeString();
        $this->save();
    }

    public function save(Array $options = [])
    {
        // Set the expiry date!
        if ($this->expires_at == null OR $this->expires_at == '0000-00-00 00:00:00') {
            $securityType = SecurityType::find($this->type);
            if ($securityType AND $securityType->expires > 0) {
                $this->attributes['expires_at'] = Carbon::now()->addDays($securityType->expires)->toDateTimeString();
            }
        }

        parent::save();
    }
}
