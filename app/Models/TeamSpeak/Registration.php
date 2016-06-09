<?php

namespace App\Models\TeamSpeak;

use App\Libraries\TeamSpeak;
use App\Models\Mship\Account;
use App\Traits\RecordsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use TeamSpeak3;

/**
 * App\Models\TeamSpeak\Registration
  
*
*@property integer                                                         $id
 * @property integer                                                       $account_id
 * @property integer                                                       $registration_ip
 * @property integer                                                       $last_ip
 * @property string                                                        $last_login
 * @property string                                                        $last_os
 * @property string                                                        $uid
 * @property integer                                                       $dbid
 * @property \Carbon\Carbon                                                $created_at
 * @property \Carbon\Carbon                                                $updated_at
 * @property string                                                        $deleted_at
 * @property-read Confirmation                                             $confirmation
 * @property-read Account                                                  $account
 * @property-read \Illuminate\Database\Eloquent\Collection|TeamsSpeakLog[] $logs
 * @property-read mixed                                                    $last_idle_message
 * @property-read mixed                                                    $last_idle_poke
 * @property-read mixed                                                    $last_nickname_warn
 * @property-read mixed                                                    $last_nickname_kick
 * @property-read mixed                                                    $last_notification_important_poke
 * @property-read mixed                                                    $last_notification_must_acknowledge_poke
 * @property-read mixed                                                    $last_notification_must_acknowledge_kick
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Registration whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Registration whereAccountId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Registration whereRegistrationIp($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Registration whereLastIp($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Registration whereLastLogin($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Registration whereLastOs($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Registration whereUid($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Registration whereDbid($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Registration whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Registration whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Registration whereDeletedAt($value)
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
            $tscon = TeamSpeak::run('VATSIM UK Registrations');
        }
        if ($this->confirmation) {
            $tscon->privilegeKeyDelete($this->confirmation->privilege_key);
            $this->confirmation->delete();
        }

        foreach ($tscon->clientList() as $client) {
            if ($client['client_database_id'] == $this->dbid || $client['client_unique_identifier'] == $this->uid) {
                $client->kick(TeamSpeak3::KICK_SERVER, 'Registration deleted.');
            }
        }

        try {
            if (is_numeric($this->dbid)) {
                $tscon->clientDeleteDb($this->dbid);
            }
        } catch (\Exception $e) {}

        parent::delete();
    }

    public function confirmation()
    {
        return $this->hasOne(Confirmation::class, 'registration_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
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
}
