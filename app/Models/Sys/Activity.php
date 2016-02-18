<?php

namespace App\Models\Sys;

use App\Models\aModel;
use \Request;

/**
 * App\Models\Sys\Activity
 *
 * @property integer $id
 * @property integer $actor_id
 * @property integer $subject_id
 * @property string $subject_type
 * @property string $action
 * @property integer $ip
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Mship\Account $actor
 * @property-read \App\Models\Sys\Activity $subject
 * @property-read mixed $type
 * @property-read mixed $extra_data
 */
class Activity extends aModel
{
    protected $table      = "sys_activity";
    protected $primaryKey = "id";
    protected $dates      = ['created_at', 'updated_at'];
    protected $fillable = ["actor_id", "subject_id", "subject_type", "action"];

    public function actor(){
        return $this->belongsTo(\App\Models\Mship\Account::class, "actor_id", "account_id");
    }

    public function subject(){
        return $this->morphTo()->withTrashed();
    }

    public function getIpAttribute(){
        return long2ip($this->attributes['ip']);
    }

    public function setIpAttribute($ip){
        $this->attributes['ip'] = ip2long($ip);
    }

    public function getTypeAttribute(){
        $strippedType = str_replace("\\", "/", $this->attributes['subject_type']);
        $strippedType = str_replace("App/Models/", "", $strippedType);

        return $strippedType;
    }

    public function getExtraDataAttribute(){
        $extraData = [];
        $extraData[$this->subject->getKeyName()] = $this->subject->getKey();

        return $extraData;
    }

    public function save(array $options = []){
        $this->ip = Request::ip();

        parent::save($options);
    }
}
