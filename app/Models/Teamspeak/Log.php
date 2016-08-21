<?php

namespace App\Models\Teamspeak;

/**
 * App\Models\Teamspeak\Log
 *
 * @property integer $id
 * @property integer $registration_id
 * @property string $type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Teamspeak\Registration $registration
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Log idleMessage()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Log idlePoke()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Log idleKick()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Log nickWarn()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Log nickKick()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Log notificationImportantPoke()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Log notificationMustAcknowledgePoke()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Log notificationMustAcknowledgeKick()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Log whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Log whereRegistrationId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Log whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Log whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Log whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Log extends \App\Models\aModel {

    protected $table = 'teamspeak_log';
    protected $primaryKey = 'id';
    protected $fillable = ['registration_id', 'type'];
    protected $dates = ['created_at', 'updated_at'];

    public function registration() {
        return $this->belongsTo("\App\Models\Teamspeak\Registration", "registration_id", "id");
    }

    public function scopeIdleMessage($query) {
        return $query->where('type', '=', 'idle_message');
    }

    public function scopeIdlePoke($query) {
        return $query->where('type', '=', 'idle_poke');
    }

    public function scopeIdleKick($query) {
        return $query->where('type', '=', 'idle_kick');
    }

    public function scopeNickWarn($query) {
        return $query->where('type', '=', 'nick_warn');
    }

    public function scopeNickKick($query) {
        return $query->where('type', '=', 'nick_kick');
    }

    public function scopeNotificationImportantPoke($query) {
        return $query->where('type', '=', 'notification_i_poke');
    }

    public function scopeNotificationMustAcknowledgePoke($query) {
        return $query->where('type', '=', 'notification_ma_poke');
    }

    public function scopeNotificationMustAcknowledgeKick($query) {
        return $query->where('type', '=', 'notification_ma_kick');
    }
}
