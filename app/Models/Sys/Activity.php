<?php

namespace App\Models\Sys;

use App\Models\Model;
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
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $subject
 * @property-read mixed $type
 * @property-read mixed $extra_data
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Activity whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Activity whereActorId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Activity whereSubjectId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Activity whereSubjectType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Activity whereAction($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Activity whereIp($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Activity whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Activity whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Activity extends Model
{
    protected $table      = "sys_activity";
    protected $primaryKey = "id";
    protected $dates      = ['created_at', 'updated_at'];
    protected $fillable = ["actor_id", "subject_id", "subject_type", "action"];

    public function actor()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class, "actor_id");
    }

    public function subject()
    {
        return $this->morphTo()->withTrashed();
    }

    public function getIpAttribute()
    {
        return long2ip($this->attributes['ip']);
    }

    public function setIpAttribute($ip)
    {
        $this->attributes['ip'] = ip2long($ip);
    }

    public function getTypeAttribute()
    {
        $strippedType = str_replace("\\", "/", $this->attributes['subject_type']);
        $strippedType = str_replace("App/Models/", "", $strippedType);

        return $strippedType;
    }

    public function getExtraDataAttribute()
    {
        $extraData = [];
        $extraData[$this->subject->getKeyName()] = $this->subject->getKey();

        return $extraData;
    }

    public function save(array $options = [])
    {
        $this->ip = Request::ip();

        return parent::save($options);
    }
}
