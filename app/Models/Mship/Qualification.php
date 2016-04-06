<?php

namespace App\Models\Mship;

use App\Models\Mship\Account;
use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use Carbon\Carbon;

/**
 * App\Models\Mship\Qualification
 *
 * @property integer        $qualification_id
 * @property string         $code
 * @property string         $type
 * @property string         $name_small
 * @property string         $name_long
 * @property string         $name_grp
 * @property integer        $vatsim
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Qualification ofType($type)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Qualification networkValue($networkValue)
 */
class Qualification extends \Eloquent
{
    use SoftDeletingTrait, RecordsActivity;
    protected $table = "mship_qualification";
    protected $primaryKey = "qualification_id";
    protected $dates = ['created_at', 'deleted_at'];
    protected $hidden = ['qualification_id'];

    public function scopeOfType($query, $type)
    {
        return $query->whereType($type);
    }

    public function scopeNetworkValue($query, $networkValue)
    {
        return $query->whereVatsim($networkValue);
    }

    public function account()
    {
        return $this->belongsToMany(Account::class, "mship_account_qualification", "qualification_id", "account_id")->withTimestamps();
    }

    public static function parseVatsimATCQualification($network)
    {
        if ($network < 1) {
            return null;
        } elseif ($network >= 8 AND $network <= 10) {
            $type = "training_atc";
        } elseif ($network >= 11) {
            $type = "admin";
        } else {
            $type = 'atc';
        }

        // Sort out the atc ratings
        $netQ = Qualification::ofType($type)->networkValue($network)->first();

        return $netQ;
    }

    public static function parseVatsimPilotQualifications($network)
    {
        $ratingsOutput = [];

        // Let's check each bitmask....
        for ($i = 0; $i <= 8; $i++) {
            $pow = pow(2, $i);
            if (($pow & $network) == $pow) {
                $ro = Qualification::ofType("pilot")->networkValue($pow)->first();
                if ($ro) {
                    $ratingsOutput[] = $ro;
                }
            }
        }

        return $ratingsOutput;
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
    }
}
