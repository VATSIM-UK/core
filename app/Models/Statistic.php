<?php

namespace App\Models;

/**
 * App\Models\Statistic
 *
 * @property int $id
 * @property string $period
 * @property string $key
 * @property string $value
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Statistic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Statistic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Statistic whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Statistic wherePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Statistic whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Statistic whereValue($value)
 * @mixin \Eloquent
 */
class Statistic extends Model
{
    protected $table = 'statistic';
    protected $primaryKey = 'id';
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = ['period', 'key'];

    public static function setStatistic($period, $key, $value)
    {
        $_s = self::where('period', '=', $period)->where('key', '=', $key)->first();
        if (!$_s) {
            $_s = new self(['period' => $period, 'key' => $key]);
        }

        $_s->value = $value;
        $_s->save();

        return $_s;
    }

    public static function getStatistic($period, $key)
    {
        $_s = self::where('period', '=', $period)->where('key', '=', $key)->first();

        if (!$_s) {
            return 0;
        }

        return $_s->value;
    }
}
