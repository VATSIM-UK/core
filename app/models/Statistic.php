<?php

namespace Models;

use \Illuminate\Database\Eloquent\SoftDeletingTrait;
use \Carbon\Carbon;

class Statistic extends \Models\aModel {

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