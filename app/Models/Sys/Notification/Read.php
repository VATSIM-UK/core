<?php

namespace App\Models\Sys;

use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class Read extends \Models\aModel {

    use SoftDeletingTrait;

    protected $table = "sys_notification_read";
    protected $primaryKey = "notification_read_id";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $hidden = ['notification_read_id'];

    const STATUS_MUST_ACKNOWLEDGE = 99; // Will interrupt login process AND ban from services until acknowledged.
    const STATUS_IMPORTANT = 70; // Will interrupt login process.
    const STATUS_OPERATIONAL = 50; // Web services
    const STATUS_GENERAL = 30; // General messages, to be read at some point.
    const STATUS_USER = 10; // User specific
    const STATUS_UNPUBLISHED = 0; // Drafts.

    public function scopeActive($query){
        return $query->where("status", "!=", self::STATUS_UNPUBLISHED);
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
}
