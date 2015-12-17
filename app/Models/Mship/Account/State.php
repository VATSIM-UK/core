<?php

namespace App\Models\Mship\Account;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class State extends \Eloquent
{

    use SoftDeletingTrait;

    protected $table      = "mship_account_state";
    protected $primaryKey = "account_state_id";
    protected $dates      = ['created_at', 'deleted_at'];
    protected $fillable   = ['state'];
    protected $hidden     = ['account_state_id'];
    protected $touches    = ['account'];

    const STATE_NOT_REGISTERED = 0;
    const STATE_GUEST          = 1;
    //const SUSPENDED = 10; @deprected in version 2.0
    //const INACTIVE = 20; @deprecated in version 2.0
    const STATE_DIVISION      = 30;
    const STATE_REGION        = 40;
    const STATE_INTERNATIONAL = 50;
    const STATE_VISITOR       = 70;
    const STATE_TRANSFER      = 60;

    public static function getStateKeyFromValue($value){
        $reflector = new \ReflectionClass(__CLASS__);
        foreach($reflector->getConstants() as $k => $v){
            if($v == $value){
                return str_replace("STATE_", "", $k);
            }
        }
        return "UNKNOWN";
    }

    public function account()
    {
        return $this->belongsTo("\App\Models\Mship\Account", "account_id", "account_id");
    }

    public function getLabelAttribute()
    {
        $lang_string = str_replace("state_", "state.", strtolower(self::getStateKeyFromValue($this->state)));
        return trans("mship.account.state.".$lang_string);
    }

    public function __toString()
    {
        return $this->getLabelAttribute();
    }

    public function save(array $options = [])
    {
        // Check it doesn't exist, first!
        $check = State::where("account_id", "=", $this->account_id)->where("state", "=", $this->state);
        if ($check->count() > 0) {
            return $check->get();
        }

        parent::save($options);

        $deleteOld = State::where("account_id", "=", $this->account_id)->where("state", "!=", $this->state)->get();
        foreach ($deleteOld as $do) {
            $do->delete();
        }

        return $this;
    }
}
