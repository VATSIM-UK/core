<?php

namespace App\Models\Mship\Ban;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reason extends Model {

    protected $primaryKey = "ban_reason_id";
    protected $table = 'mship_ban_reason';
    public $timestamps = true;

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public function bans()
    {
        return $this->hasMany('\App\Models\Mship\Account\Ban', 'ban_reason_id', 'reason_id');
    }

    public function getPeriodHoursAttribute(){
        if($this->attributes['period_unit'] == "H"){
            return $this->attributes['period_amount'];
        }

        if($this->attributes['period_unit'] == "D"){
            return $this->attributes['period_amount'] * 24;
        }

        if($this->attributes['period_unit'] == "M"){
            return $this->attributes['period_amount'] * 730.001;
        }
    }

    public function __toString(){
        return $this->name . " (Duration ".$this->period_amount.$this->period_unit.")";
    }

}