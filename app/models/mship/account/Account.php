<?php

namespace Models\Mship\Account;

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use \Enums\Account\Status;
use \Carbon\Carbon;

class Account extends \Models\aTimelineEntry {

    use SoftDeletingTrait;

    protected $table = "mship_account";
    protected $primaryKey = "account_id";
    protected $dates = ['joined_at', 'created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['account_id', 'name_first', 'name_last'];
    protected $attributes = ['name_first' => '', 'name_last' => '', 'status' => 0];

    public function emails() {
        return $this->hasMany("\Models\Mship\Account\Email", "account_id", "account_id");
    }

    public function notes() {
        return $this->hasMany("\Models\Mship\Account\Notes", "account_id", "account_id");
    }

    public function notesActioner() {
        return $this->hasMany("\Models\Mship\Account\Notes", "actioner_id", "account_id");
    }

    public function tokens() {
        return $this->morphMany("\Models\Sys\Token", "related");
    }

    public function qualifications($type = null) {
        $query = $this->hasMany("\Models\Mship\Account\Qualification", "account_id", "account_id");

        switch ($type):
            case "atc":
                $query->where(function($q) {
                    $q->whereHas("qualification", function($p) {
                        $p->where("type", "=", "atc");
                    });
                });
                break;
            case "pilot":
                $query->where(function($q) {
                    $q->whereHas("qualification", function($p) {
                        $p->where("type", "=", "pilot");
                    });
                });
                break;
            case "training_atc":
                $query->where(function($q) {
                    $q->whereHas("qualification", function($p) {
                        $p->where("type", "=", "training_atc");
                    });
                });
                break;
            case "training_pilot":
                $query->where(function($q) {
                    $q->whereHas("qualification", function($p) {
                        $p->where("type", "=", "training_pilot");
                    });
                });
                break;
            case "admin":
                $query->where(function($q) {
                    $q->whereHas("qualification", function($p) {
                        $p->where("type", "=", "admin");
                    });
                });
                break;
        endswitch;

        return $query;
    }

    public function qualificationsAtc() {
        return $this->qualifications("atc");
    }

    public function getQualificationAtcObjAttribute(){
        $a = $this->qualificationsAtc()->orderBy("created_at", "DESC")->first();
        return $a;
    }

    public function getQualificationAtcAttribute(){
        $a = $this->qualificationsAtcObj();
        return $a ? $a->qualification->name_long : "";
    }

    public function qualificationsAtcTraining() {
        return $this->qualifications("training_atc");
    }

    public function qualificationsPilot() {
        return $this->qualifications("pilot");
    }

    public function getQualificationPilotAttribute(){
        $output = "None";
        foreach($this->qualificationsPilot()->get() as $p){
            $output.= $p->code.", ";
        }
        return rtrim($output, ", ");
    }

    public function qualificationsPilotTraining() {
        return $this->qualifications("training_pilot");
    }

    public function qualificationsAdmin() {
        return $this->qualifications("admin");
    }

    public function states() {
        return $this->hasMany("\Models\Mship\Account\State", "account_id", "account_id");
    }

    public function getIsStateAttribute($state) {
        return $this->states()->where("state", "=", $state);
    }

    public function getCurrentStateAttribute() {
        return $this->states()->first();
    }

    public function getPrimaryStateAttribute() {
        return $this->states()->first();
    }

    public function ssoTokens() {
        return $this->hasMany("\Models\Sso\Token", "account_id", "account_id");
    }

    public function security() {
        return $this->hasMany("\Models\Mship\Account\Security", "account_id", "account_id");
    }

    public function getCurrentSecurityAttribute() {
        return $this->security()->first();
    }

    public function setPassword($password, $type) {
        if ($this->current_security) {
            $this->current_security->delete();
        }

        // Set a new one!
        $security = new Security;
        $security->account_id = $this->account_id;
        $security->security_id = $type->security_id;
        $security->value = $password;
        $security->save();
    }

    public function addEmail($newEmail, $verified = 0, $primary = 0) {
        // Check this email doesn't exist for this user already.
        $check = $this->emails()->where("email", "LIKE", $newEmail);
        if ($check->count() < 1) {
            $email = new Email;
            $email->email = $newEmail;
            if ($verified) {
                $email->verified = Carbon::now()->toDateTimeString();
            }
            $this->emails()->save($email);
            $isNewEmail = true;
        } else {
            $email = $check->first();
            $isNewEmail = false;
        }

        if ($primary) {
            $email->is_primary = 1;
            $email->save();
        }
        return $isNewEmail;
    }

    public function addQualification($qualificationType) {
        if(!$qualificationType){
            return false;
        }

        // Does this rating already exist?
        if ($this->qualifications()->where("qualification_id", "=", $qualificationType->qualification_id)->count() > 0) {
            return false;
        }

        // Let's add it!
        $qual = new Qualification;
        $qual->qualification_id = $qualificationType->qualification_id;
        $this->qualifications()->save($qual);

        return true;
    }

    public function setStatusFlag($flag) {
        $status = $this->attributes['status'];
        $status |= $flag;
        $this->attributes['status'] = $status;
    }

    public function unSetStatusFlag($flag) {
        $status = $this->attributes['status'];
        $status = $status ^ $flag;
        $this->attributes['status'] = $status;
    }

    public function getIsSystemBannedAttribute() {
        $status = $this->attributes['status'];
        return (boolean) (Status::SYSTEM_BANNED & $status);
    }

    public function setIsSystemBannedAttribute($value) {
        if ($value && !$this->is_system_banned) {
            $this->setStatusFlag(Status::SYSTEM_BANNED);
        } elseif($this->is_system_banned) {
            $this->unSetStatusFlag(Status::SYSTEM_BANNED);
        }
    }

    public function getIsNetworkBannedAttribute() {
        $status = $this->attributes['status'];
        return (boolean) ((Status::NETWORK_SUSPENDED & $status));
    }

    public function setIsNetworkBannedAttribute($value) {
        if ($value && !$this->is_network_banned) {
            $this->setStatusFlag(Status::NETWORK_SUSPENDED);
        } elseif($this->is_network_banned) {
            $this->unSetStatusFlag(Status::NETWORK_SUSPENDED);
        }
    }

    public function getIsBannedAttribute() {
        return $this->is_system_banned OR $this->is_network_banned;
    }

    public function getIsInactive() {
        $status = $this->attributes['status'];
        return (boolean) (Status::INACTIVE & $status);
    }

    public function setIsInactiveAttribute($value) {
        if ($value && !$this->is_inactive) {
            $this->setStatusFlag(Status::INACTIVE);
        } elseif($this->is_inactive) {
            $this->unSetStatusFlag(Status::INACTIVE);
        }
    }

    public function getIsSystem() {
        $status = $this->attributes['status'];
        return (boolean) (Status::SYSTEM & $status);
    }

    public function setIsSystemAttribute($value) {
        if ($value && !$this->is_system) {
            $this->setStatusFlag(Status::SYSTEM);
        } elseif($this->is_system) {
            $this->unSetStatusFlag(Status::SYSTEM);
        }
    }

    public function getStatusAttribute() {
        if ($this->is_system_banned) {
            return Status::getDescription(Status::SYSTEM_BANNED);
        } elseif ($this->is_network_banned) {
            return Status::getDescription(Status::NETWORK_SUSPENDED);
        } elseif ($this->is_inactive) {
            return Status::getDescription(Status::INACTIVE);
        } elseif ($this->is_system) {
            return Status::getDescription(Status::SYSTEM);
        } else {
            return Status::getDescription(Status::ACTIVE);
        }
    }

    public function getStatusArrayAttribute() {
        $stati = array();
        if ($this->is_system_banned) {
            $stati[] = Status::getDescription(Status::SYSTEM_BANNED);
        }

        if ($this->is_network_banned) {
            $stati[] = Status::getDescription(Status::NETWORK_SUSPENDED);
        }

        if ($this->is_inactive) {
            $stati[] = Status::getDescription(Status::INACTIVE);
        }

        if ($this->is_system) {
            $stati[] = Status::getDescription(Status::SYSTEM);
        }

        if (count($stati) < 1) {
            $stati[] = Status::getDescription(Status::ACTIVE);
        }
        return $stati;
    }

    public function getRequiresCertUpdateAttribute() {
        if ($this->attributes['checked'] == NULL) {
            return true;
        }

        if (Carbon::createFromFormat("Y-m-d H:i:s")->diffInDays() > '2') {
            return true;
        }

        return false;
    }

    public function getLastLoginIpAttribute() {
        return long2ip($this->attributes['last_login_ip']);
    }

    public function setLastLoginIpAttribute($value) {
        $this->attributes['last_login_ip'] = ip2long($value);
    }

    public function getPrimaryEmailAttribute() {
        foreach ($this->emails as $e) {
            if ($e->is_primary) {
                return $e;
            }
        }
        return new \Models\Mship\Account\Email();
    }

    public function getSecondaryEmailAttribute(){
        return $this->emails()->where("is_primary", "=", "0")->get();
    }

    public function setNameFirstAttribute($value) {
        $value = trim($value);
        $value = strtolower($value);
        $value = ucfirst($value);
        $this->attributes["name_first"] = utf8_encode($value);
    }

    public function setNameLastAttribute($value) {
        $value = trim($value);
        $value = strtolower($value);

        // Let's fix McSomebody and MacSomebody
        if(substr($value, 0, 2) == "mc"){
            $value = "Mc".ucfirst(substr($value, 3));
        } elseif(substr($value, 0, 3) == "mac"){
            $value = "Mac".ucfirst(substr($value, 4));
        } else {
            $value = ucfirst($value);
        }

        $this->attributes["name_last"] = utf8_encode($value);
    }

    public function getNameAttribute() {
        return $this->attributes['name_first'] . " " . $this->attributes['name_last'];
    }

    public function renewSalt() {
        $salt = md5(uniqid() . md5(time()));
        $salt = substr($salt, 0, 20);
        $this->salt = $salt;
        $this->save();
        return $salt;
    }

    public function getDisplayValueAttribute() {
        return $this->getNameAttribute() . " (" . $this->attributes['account_id'] . ")";
    }

    public function toArray() {
        $array = parent::toArray();
        $array["name"] = $this->name;
        $array["email"] = $this->primary_email->email;
        $array['atc_rating'] = $this->qualificationsAtc()->orderBy("created_at", "DESC")->first();
        $array['atc_rating'] = ($array['atc_rating'] ? $array['atc_rating']->qualification->name_long : "");
        $array['pilot_rating'] = array();
        foreach($this->qualifications_pilot as $rp){
            $array['pilot_rating'][] = $rp->qualification->code;
        }
        $array['pilot_rating'] = implode(", ", $array['pilot_rating']);
        return $array;
    }

    public function setCertStatus($certRatingInt) {
        if ($certRatingInt < 0) {
            $this->is_inactive = true;
            $this->is_network_banned = false;
        } elseif ($certRatingInt == 0) {
            $this->is_network_banned = true;
            $this->is_inactive = false;
        } else {
            $this->is_inactive = false;
            $this->is_network_banned = false;
        }
    }

    public function determineState($region, $division){
        if($region == "EUR" AND $division == "GBR"){
            $state = \Enums\Account\State::DIVISION;
        } elseif($region == "EUR"){
            $state = \Enums\Account\State::REGION;
        } else {
            $state = \Enums\Account\State::INTERNATIONAL;
        }
        if($this->account_id < 1){
            print_r($this); exit();
        }
        $this->states()->save(new State(array("state" => $state)));
    }

}
