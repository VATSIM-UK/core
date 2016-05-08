<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use Carbon\Carbon;

/**
 * App\Models\Statistic
 *
 * @property integer $statistic_id
 * @property string $period
 * @property string $key
 * @property string $value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Statistic whereStatisticId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Statistic wherePeriod($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Statistic whereKey($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Statistic whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Statistic whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Statistic whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Statistic whereDeletedAt($value)
 * @mixin \Eloquent
 */
class Statistic extends \App\Models\aModel {

    use SoftDeletingTrait;

    protected $table = "statistic";
    protected $primaryKey = "statistic_id";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['period', 'key'];

    public static function setStatistic($period, $key, $value) {
        $_s = Statistic::where("period", "=", $period)->where("key", "=", $key)->first();
        if(!$_s){
            $_s = new Statistic(array("period" => $period, "key" => $key));
        }

        $_s->value = $value;
        $_s->save();
        return $_s;
    }

    public static function getStatistic($period, $key){
        $_s = Statistic::where("period", "=", $period)->where("key", "=", $key)->first();

        if(!$_s){
            return 0;
        }

        return $_s->value;
    }

}