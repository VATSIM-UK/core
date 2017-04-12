<?php

namespace App\Models\Mship;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Mship\Qualification
 *
 * @property int $id
 * @property string $code
 * @property string $type
 * @property string $name_small
 * @property string $name_long
 * @property string $name_grp
 * @property int $vatsim
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account[] $account
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Qualification networkValue($networkValue)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Qualification ofType($type)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Qualification whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Qualification whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Qualification whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Qualification whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Qualification whereNameGrp($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Qualification whereNameLong($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Qualification whereNameSmall($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Qualification whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Qualification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Qualification whereVatsim($value)
 * @mixin \Eloquent
 */
class Qualification extends \Eloquent
{
    use SoftDeletingTrait, RecordsActivity;
    protected $table = 'mship_qualification';
    protected $primaryKey = 'id';
    protected $dates = ['created_at', 'deleted_at'];
    protected $hidden = ['id'];

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
        return $this->belongsToMany(Account::class, 'mship_account_qualification', 'qualification_id', 'account_id')->withTimestamps();
    }

    public static function parseVatsimATCQualification($network)
    {
        $network = intval($network);
        if ($network < 1) {
            return;
        } elseif ($network >= 8 and $network <= 10) {
            $type = 'training_atc';
        } elseif ($network >= 11) {
            $type = 'admin';
        } else {
            $type = 'atc';
        }

        // Sort out the atc ratings
        $netQ = self::ofType($type)->networkValue($network)->first();

        return $netQ;
    }

    public static function parseVatsimPilotQualifications($network)
    {
        $ratingsOutput = [];

        // Let's check each bitmask....
        for ($i = 0; $i <= 8; $i++) {
            $pow = pow(2, $i);
            if (($pow & $network) == $pow) {
                $ro = self::ofType('pilot')->networkValue($pow)->first();
                if ($ro) {
                    $ratingsOutput[] = $ro;
                }
            }
        }

        return $ratingsOutput;
    }

    public function __toString()
    {
        return $this->code;
    }
}
