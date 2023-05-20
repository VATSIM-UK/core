<?php

namespace App\Models\Mship;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Mship\Qualification.
 *
 * @property int $id
 * @property string $code
 * @property string $type
 * @property string $name_small
 * @property string $name_long
 * @property string $name_grp
 * @property int $vatsim
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account[] $account
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read mixed $is_c1
 * @property-read mixed $is_c3
 * @property-read mixed $is_o_b_s
 * @property-read mixed $is_s1
 * @property-read mixed $is_s2
 * @property-read mixed $is_s3
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Qualification networkValue($networkValue)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Qualification ofType($type)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Qualification whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Qualification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Qualification whereNameGrp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Qualification whereNameLong($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Qualification whereNameSmall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Qualification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Qualification whereVatsim($value)
 *
 * @mixin \Eloquent
 */
class Qualification extends Model
{
    use HasFactory;

    protected $table = 'mship_qualification';

    protected $primaryKey = 'id';

    protected $hidden = ['id'];

    public $timestamps = false;

    public function scopeCode($query, $code)
    {
        return $query->where('code', $code);
    }

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
        return $this->belongsToMany(Account::class, 'mship_account_qualification', 'qualification_id', 'account_id')
            ->using(AccountQualification::class)
            ->wherePivot('deleted_at', '=', null)
            ->withTimestamps();
    }

    public static function parseVatsimATCQualification($network): ?self
    {
        $network = (int) $network;
        if ($network < 1) {
            return null;
        } elseif ($network >= 8 and $network <= 10) {
            $type = 'training_atc';
        } elseif ($network >= 11) {
            $type = 'admin';
        } else {
            $type = 'atc';
        }

        // Sort out the atc ratings
        return self::ofType($type)->networkValue($network)->first();
    }

    public static function parseVatsimPilotQualifications($network)
    {
        $ratingsOutput = [];

        // A P0 will not be picked up in the bitmap
        if ($network >= 0) {
            array_push($ratingsOutput, self::ofType('pilot')->networkValue(0)->first());
        }

        // Let's check each bitmask....
        for ($i = 0; $i <= 8; $i++) {
            $pow = pow(2, $i);
            if (($pow & $network) == $pow) {
                $ro = self::ofType('pilot')->networkValue($pow)->first();
                if ($ro) {
                    array_push($ratingsOutput, $ro);
                }
            }
        }

        return $ratingsOutput;
    }

    public function __toString()
    {
        return $this->code;
    }

    public function getIsOBSAttribute()
    {
        return $this->code == 'OBS';
    }

    public function getIsS1Attribute()
    {
        return $this->code == 'S1';
    }

    public function getIsS2Attribute()
    {
        return $this->code == 'S2';
    }

    public function getIsS3Attribute()
    {
        return $this->code == 'S3';
    }

    public function getIsC1Attribute()
    {
        return $this->code == 'C1';
    }

    public function getIsC3Attribute()
    {
        return $this->code == 'C3';
    }
}
