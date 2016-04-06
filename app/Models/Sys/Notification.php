<?php

namespace App\Models\Sys;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Sys\Notification
 *
 * @property integer $notification_id
 * @property string $title
 * @property string $content
 * @property integer $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $effective_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account[] $readBy
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Notification published()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Notification withStatus($status)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Notification mustAcknowledge()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Notification important()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Notification operational()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Notification general()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Notification user()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sys\Notification since($sinceTimestamp)
 */
class Notification extends \App\Models\aModel {

    use SoftDeletingTrait;

    protected $table = "sys_notification";
    protected $primaryKey = "notification_id";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['notification_id'];

    const STATUS_MUST_ACKNOWLEDGE = 99; // Will interrupt login process AND ban from services until acknowledged.
    const STATUS_IMPORTANT = 70; // Will interrupt login process.
    const STATUS_OPERATIONAL = 50; // Web services
    const STATUS_GENERAL = 30; // General messages, to be read at some point.
    const STATUS_USER = 10; // User specific
    const STATUS_UNPUBLISHED = 0; // Drafts.

    public function scopePublished($query){
        return $query->where("status", "!=", self::STATUS_UNPUBLISHED)
                     ->where("effective_at", "<=", \Carbon\Carbon::now());
    }

    public function scopeWithStatus($query, $status){
        return $query->where("status", "=", $status);
    }

    public function scopeMustAcknowledge($query){
        return $query->withStatus(self::STATUS_MUST_ACKNOWLEDGE);
    }

    public function scopeImportant($query){
        return $query->withStatus(self::STATUS_IMPORTANT);
    }

    public function scopeOperational($query){
        return $query->withStatus(self::STATUS_OPERATIONAL);
    }

    public function scopeGeneral($query){
        return $query->withStatus(self::STATUS_GENERAL);
    }

    public function scopeUser($query){
        return $query->withStatus(self::STATUS_USER);
    }

    public function scopeSince($query, $sinceTimestamp){
        if(!($sinceTimestamp instanceof \Carbon\Carbon)){
            $sinceTimestamp = \Carbon\Carbon::parse($sinceTimestamp);
        }

        return $query->where("effective_at", ">=", $sinceTimestamp);
    }

    public function readBy(){
        return $this->belongsToMany("\App\Models\Mship\Account", "sys_notification_read", "notification_id")->with("created_at", "updated_at");
    }
}
