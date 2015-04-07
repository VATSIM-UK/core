<?php

namespace Models\Teamspeak;

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use Controllers\Teamspeak\TeamspeakAdapter;
use Models\Teamspeak\Log;
use TeamSpeak3;
use Carbon\Carbon;

class Registration extends \Models\aModel {

    use SoftDeletingTrait;

    protected $table = 'teamspeak_registration';
    protected $primaryKey = 'id';
    protected $fillable = ['*'];
    protected $attributes = ['registration_ip' => '0', 'last_ip' => '0'];
    protected $dates = ['created_at', 'updated_at'];

    public function delete($tscon = NULL) {
        if ($tscon == NULL) $tscon = TeamspeakAdapter::run("VATSIM UK Registrations");
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

        parent::delete();
    }

    public function confirmation() {
        return $this->hasOne("\Models\Teamspeak\Confirmation", "registration_id", "id");
    }

    public function account() {
        return $this->belongsTo("\Models\Mship\Account", "account_id", "account_id");
    }

    public function logs() {
        return $this->hasMany("\Models\Teamspeak\Log", "registration_id", "id");
    }

    public function setRegistrationIpAttribute($value) {
        $this->attributes['registration_ip'] = ip2long($value);
    }

    public function getRegistrationIpAttribute() {
        return long2ip($this->attributes['registration_ip']);
    }

    public function setLastIpAttribute($value) {
        $this->attributes['last_ip'] = ip2long($value);
    }

    public function getLastIpAttribute() {
        return long2ip($this->attributes['last_ip']);
    }

    public function getLastIdleMessageAttribute() {
        $m = $this->logs()->idleMessage()->orderBy('created_at', 'desc')->first();
        if (!$m) return Carbon::createFromTimeStampUTC(0);
        else return $m->created_at;
    }

    public function getLastIdlePokeAttribute() {
        $m = $this->logs()->idlePoke()->orderBy('created_at', 'desc')->first();
        if (!$m) return Carbon::createFromTimeStampUTC(0);
        else return $m->created_at;
    }

    public function getLastNicknameWarnAttribute() {
        $m = $this->logs()->nickWarn()->orderBy('created_at', 'desc')->first();
        if (!$m) return Carbon::createFromTimeStampUTC(0);
        else return $m->created_at;
    }

    public function getLastNicknameKickAttribute() {
        $m = $this->logs()->nickKick()->orderBy('created_at', 'desc')->first();
        if (!$m) return Carbon::createFromTimeStampUTC(0);
        else return $m->created_at;
    }

}
