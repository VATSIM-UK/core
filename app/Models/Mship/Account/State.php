<?php

namespace App\Models\Mship\Account;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Mship\Account\State
 *
 * @property integer                        $id
 * @property integer                        $account_id
 * @property boolean                        $state
 * @property \Carbon\Carbon                 $created_at
 * @property \Carbon\Carbon                 $updated_at
 * @property \Carbon\Carbon                 $deleted_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read mixed                     $label
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\State whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\State whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\State whereState($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\State whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\State whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account\State whereDeletedAt($value)
 * @mixin \Eloquent
 */
class State extends \Eloquent
{

    use RecordsActivity;

    protected $table      = "mship_account_state";
    protected $primaryKey = "id";
    protected $dates      = ['started_at', 'ended_at', 'created_at'];
    protected $fillable   = ['state', 'region', 'division', 'started_at', 'ended_at'];
    protected $hidden     = ['id'];
    protected $touches    = ['account'];

    const STATE_NOT_REGISTERED = 0;
    const STATE_GUEST          = 1;
    //const SUSPENDED = 10; @deprected in version 2.0
    //const INACTIVE = 20; @deprecated in version 2.0
    const STATE_DIVISION      = 30;
    const STATE_REGION        = 40;
    const STATE_INTERNATIONAL = 50;
    const STATE_TRANSFERRING  = 60;
    const STATE_VISITOR       = 70;

    public static function getStateKeyFromValue($value)
    {
        $reflector = new \ReflectionClass(__CLASS__);
        foreach ($reflector->getConstants() as $k => $v) {
            if ($v == $value) {
                return str_replace("STATE_", "", $k);
            }
        }

        return "UNKNOWN";
    }

    public function account()
    {
        return $this->belongsTo("\App\Models\Mship\Account", "account_id");
    }

    public function getLabelAttribute()
    {
        $lang_string = str_replace("state_", "state.", strtolower(self::getStateKeyFromValue($this->state)));

        return trans("mship.account.state." . $lang_string);
    }

    public function __toString()
    {
        return $this->getLabelAttribute();
    }

    public function save(array $options = [])
    {
        // Check it doesn't exist, first!
        $check = State::where("account_id", "=", $this->account_id)
                      ->where("state", "=", $this->state)
                      ->where("region", "=", $this->region)
                      ->where("division", "=", $this->division);

        if ($check->count() > 0) {
            return $check->get();
        }

        parent::save($options);

        // Does the old need to be deleted?
        $isUkDivision = strcasecmp($this->division, "GBR") == 0;
        $isVisitor = $this->account->has_visitor_state;
        $isTransferring = $this->account->has_transferring_state;

        // 1) If the old is visitor or transferring, then no.
        if ($this->state == self::STATE_VISITOR || $this->state == self::STATE_TRANSFERRING) {
            return;
        }

        // 2) If they're now part of the UK but have a T flag, then yes.
        if ($isUkDivision && $isTransferring) {
            $transState = State::where("account_id", "=", $this->account_id)
                               ->where("state", "=", self::STATE_TRANSFERRING)
                               ->first();

            $transState->end();
        }

        // 2) If they're now part of the UK but have a V flag, then yes.
        if ($isUkDivision && $isVisitor) {
            $visitState = State::where("account_id", "=", $this->account_id)
                               ->where("state", "=", self::STATE_VISITOR)
                               ->first();

            $visitState->end();
        }

        if($this->region)

        return $this;
    }

    public function end()
    {
        $this->attributes['ended_at'] = \Carbon\Carbon::now();
        $this->save();
    }
}
