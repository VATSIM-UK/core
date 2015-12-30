<?php

namespace App\Models\Mship\Ban;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reason extends Model {

    protected $table = 'mship_ban_reason';
    public $timestamps = true;

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public function bans()
    {
        return $this->hasMany('\App\Models\Mship\Account\Ban', 'ban_reason_id', 'reason_id');
    }

    public function __toString(){
        return $this->reason_text;
    }

}