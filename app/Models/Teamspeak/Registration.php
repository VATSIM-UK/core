<?php

namespace App\Models\Teamspeak;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use App\Http\Controllers\Teamspeak\TeamspeakAdapter;
use App\Models\Teamspeak\Log;
use TeamSpeak3;
use Carbon\Carbon;

/**
 * App\Models\Teamspeak\Registration
 *
 * @property integer                                                                   $id
 * @property integer                                                                   $account_id
 * @property integer                                                                   $registration_ip
 * @property integer                                                                   $last_ip
 * @property string                                                                    $last_login
 * @property string                                                                    $last_os
 * @property string                                                                    $uid
 * @property integer                                                                   $dbid
 * @property string                                                                    $status
 * @property \Carbon\Carbon                                                            $created_at
 * @property \Carbon\Carbon                                                            $updated_at
 * @property string                                                                    $deleted_at
 * @property-read \App\Models\Teamspeak\Confirmation                                   $confirmation
 * @property-read \App\Models\Mship\Account                                            $account
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Teamspeak\Log[] $logs
 * @property-read mixed                                                                $last_idle_message
 * @property-read mixed                                                                $last_idle_poke
 * @property-read mixed                                                                $last_nickname_warn
 * @property-read mixed                                                                $last_nickname_kick
 * @property-read mixed
 *                $last_notification_important_poke
 * @property-read mixed
 *                $last_notification_must_acknowledge_poke
 * @property-read mixed
 *                $last_notification_must_acknowledge_kick
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Registration whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Registration whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Registration whereRegistrationIp($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Registration whereLastIp($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Registration whereLastLogin($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Registration whereLastOs($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Registration whereUid($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Registration whereDbid($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Registration whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Registration whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Registration whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Teamspeak\Registration whereDeletedAt($value)
 * @mixin \Eloquent
 */
class Registration extends \App\Models\aModel
{

    use SoftDeletingTrait, RecordsActivity;

    protected $table      = 'teamspeak_registration';
    protected $primaryKey = 'id';
    protected $fillable   = ['*'];
    protected $attributes = ['registration_ip' => '0', 'last_ip' => '0'];
    protected $dates      = ['created_at', 'updated_at'];

    public function delete($tscon = null)
    {
        if ($tscon == null) {
            $tscon = TeamspeakAdapter::run("VATSIM UK Registrations");
        }
        if ($this->confirmation) {
            $tscon->privilegeKeyDelete($this->confirmation->privilege_key);
            $this->confirmation->delete();
        }

        foreach ($tscon->clientList() as $client) {
            if ($client['client_database_id'] == $this->dbid || $client['client_unique_identifier'] == $this->uid) {
                $client->kick(TeamSpeak3::KICK_SERVER, "Registration deleted.");
            }
        }

        $this->status = 'deleted';
        $this->save();

        try {
            if (is_numeric($this->dbid)) {
                $tscon->clientDeleteDb($this->dbid);
            }
        } catch (\Exception $e) {
            //
        }

        parent::delete();
    }

    public function confirmation()
    {
        return $this->hasOne("\App\Models\Teamspeak\Confirmation", "registration_id", "id");
    }

    public function account()
    {
        return $this->belongsTo("\App\Models\Mship\Account", "account_id");
    }

    public function logs()
    {
        return $this->hasMany("\App\Models\Teamspeak\Log", "registration_id", "id");
    }

    public function setRegistrationIpAttribute($value)
    {
        $this->attributes['registration_ip'] = ip2long($value);
    }

    public function getRegistrationIpAttribute()
    {
        return long2ip($this->attributes['registration_ip']);
    }

    public function setLastIpAttribute($value)
    {
        $this->attributes['last_ip'] = ip2long($value);
    }

    public function getLastIpAttribute()
    {
        return long2ip($this->attributes['last_ip']);
    }

    public function getLastIdleMessageAttribute()
    {
        $m = $this->logs()->idleMessage()->orderBy('created_at', 'desc')->first();
        if (!$m) {
            return Carbon::createFromTimeStampUTC(0);
        } else {
            return $m->created_at;
        }
    }

    public function getLastIdlePokeAttribute()
    {
        $m = $this->logs()->idlePoke()->orderBy('created_at', 'desc')->first();
        if (!$m) {
            return Carbon::createFromTimeStampUTC(0);
        } else {
            return $m->created_at;
        }
    }

    public function getLastNicknameWarnAttribute()
    {
        $m = $this->logs()->nickWarn()->orderBy('created_at', 'desc')->first();
        if (!$m) {
            return Carbon::createFromTimeStampUTC(0);
        } else {
            return $m->created_at;
        }
    }

    public function getLastNicknameKickAttribute()
    {
        $m = $this->logs()->nickKick()->orderBy('created_at', 'desc')->first();
        if (!$m) {
            return Carbon::createFromTimeStampUTC(0);
        } else {
            return $m->created_at;
        }
    }

    public function getLastNotificationImportantPokeAttribute()
    {
        $m = $this->logs()->notificationImportantPoke()->orderBy('created_at', 'desc')->first();
        if (!$m) {
            return Carbon::createFromTimeStampUTC(0);
        } else {
            return $m->created_at;
        }
    }

    public function getLastNotificationMustAcknowledgePokeAttribute()
    {
        $m = $this->logs()->notificationMustAcknowledgePoke()->orderBy('created_at', 'desc')->first();
        if (!$m) {
            return Carbon::createFromTimeStampUTC(0);
        } else {
            return $m->created_at;
        }
    }

    public function getLastNotificationMustAcknowledgeKickAttribute()
    {
        $m = $this->logs()->notificationMustAcknowledgeKick()->orderBy('created_at', 'desc')->first();
        if (!$m) {
            return Carbon::createFromTimeStampUTC(0);
        } else {
            return $m->created_at;
        }
    }

}
