<?php

namespace Models\Mship;

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Database\Eloquent\SoftDeletingTrait;
use \Carbon\Carbon;
use \Models\Mship\Account\Email as AccountEmail;
use \Models\Mship\Account\Qualification as AccountQualification;
use \Models\Sys\Token as SystemToken;
use \Models\Mship\Role as RoleData;
use \Models\Mship\Permission as PermissionData;

class Account extends \Models\aTimelineEntry implements UserInterface {

    use UserTrait, SoftDeletingTrait;

    protected $table = "mship_account";
    protected $primaryKey = "account_id";
    public $incrementing = false;
    protected $dates = ['joined_at', 'created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['account_id', 'name_first', 'name_last'];
    protected $attributes = ['name_first' => '', 'name_last' => '', 'status' => self::STATUS_ACTIVE];

    const STATUS_ACTIVE = 0; //b"00000";
    const STATUS_SYSTEM_BANNED = 1; //b"0001";
    const STATUS_NETWORK_SUSPENDED = 2; //b"0010";
    const STATUS_INACTIVE = 4; //b"0100";
    const STATUS_LOCKED = 8; //b"1000";
    const STATUS_SYSTEM = 8; //b"1000"; // Alias of LOCKED

    public static function getStatusDescription($value) {
        switch ($value) {
            case self::STATUS_ACTIVE:
                return "Active";
            case self::STATUS_SYSTEM_BANNED:
                return "Banned (LOCAL)";
            case self::STATUS_NETWORK_SUSPENDED:
                return "Suspended (NETWORK)";
            case self::STATUS_INACTIVE:
                return "Inactive";
            case self::STATUS_LOCKED:
            case self::STATUS_SYSTEM:
                return "Locked/System";
            default:
                return "Unknown Status";
        }
    }

    public static function eventCreated($model, $extra=null, $data=null){
        parent::eventCreated($model, $extra, $data);

        // Add the user to the default role.
        $defaultRole = RoleData::isDefault()->first();
        if($defaultRole){
            $model->roles()->attach($defaultRole);
        }

        // Generate an email to the user to advise them of their new account at VATUK.
        \Models\Sys\Postmaster\Queue::queue("MSHIP_ACCOUNT_CREATED", $model->account_id, VATUK_ACCOUNT_SYSTEM, $model->toArray());
    }

    public static function scopeIsSystem($query){
        return $query->where(\DB::raw(self::STATUS_SYSTEM."&`status`"), "=", self::STATUS_SYSTEM);
    }

    public static function scopeIsNotSystem($query){
        return $query->where(\DB::raw(self::STATUS_SYSTEM."&`status`"), "!=", self::STATUS_SYSTEM);
    }

    public function dataChanges(){
        return $this->morphMany("\Models\Sys\Data\Change", "model")->orderBy("created_at", "DESC");
    }

    public function emails() {
        return $this->hasMany("\Models\Mship\Account\Email", "account_id", "account_id");
    }

    public function messagesReceived() {
        return $this->hasMany("\Models\Sys\Postmaster\Queue", "recipient_id", "account_id")->orderBy("created_at", "DESC");
    }

    public function messagesSent() {
        return $this->hasMany("\Models\Sys\Postmaster\Queue", "sender_id", "account_id")->orderBy("created_at", "DESC");
    }

    public function notes() {
        return $this->hasMany("\Models\Mship\Account\Note", "account_id", "account_id")->orderBy("created_at", "DESC");
    }

    public function noteWriter() {
        return $this->hasMany("\Models\Mship\Account\Note", "writer_id", "account_id");
    }

    public function tokens() {
        return $this->morphMany("\Models\Sys\Token", "related");
    }

    public function qualifications() {
        return $this->hasMany("\Models\Mship\Account\Qualification", "account_id", "account_id");
    }

    public function roles(){
        return $this->belongsToMany("\Models\Mship\Role", "mship_account_role")->with("permissions");
    }

    public function states() {
        return $this->hasMany("\Models\Mship\Account\State", "account_id", "account_id");
    }

    public function ssoTokens() {
        return $this->hasMany("\Models\Sso\Token", "account_id", "account_id");
    }

    public function security() {
        return $this->hasMany("\Models\Mship\Account\Security", "account_id", "account_id");
    }

    public function getQualificationAtcAttribute() {
        $a = $this->qualifications()->atc()->orderBy("created_at", "DESC")->first();
        return $a;
    }

    public function getQualificationsAtcAttribute() {
        $a = $this->qualifications()->atc()->orderBy("created_at", "DESC")->get();
        return $a;
    }

    public function getQualificationsAtcTrainingAttribute() {
        return $this->qualifications()->atcTraining()->orderBy("created_at", "DESC")->get();
    }

    public function getQualificationsPilotAttribute() {
        return $this->qualifications()->pilot()->orderBy("created_at", "DESC")->get();
    }

    public function getQualificationsPilotStringAttribute(){
        $output = "";
        foreach ($this->qualifications_pilot as $p) {
            $output.= $p->qualification->code . ", ";
        }
        if($output == ""){
            $output = "None";
        }
        return rtrim($output, ", ");
    }

    public function getQualificationsPilotTrainingAttribute() {
        return $this->qualifications()->pilotTraining()->orderBy("created_at", "DESC")->get();
    }

    public function getQualificationsAdminAttribute() {
        return $this->qualifications()->admin()->orderBy("created_at", "DESC")->get();
    }

    public function getIsStateAttribute($state) {
        return $this->states()->where("state", "=", $state);
    }

    public function getCurrentStateAttribute() {
        return $this->states()->first();
    }

    public function getAllStatesAttribute(){
        $return = array();

        foreach($this->states as $state){
            $key = strtolower(\Enums\Account\State::valueToKey($state->state));
            $return[$key] = 1;
            $return[$key."_date"] = $state->created_at->toDateTimeString();
        }
        return $return;
    }

    public function getPrimaryStateAttribute() {
        return $this->states()->first();
    }

    public function getCurrentSecurityAttribute() {
        return $this->security()->first();
    }
    public function hasPermission($permission){
        if(!is_numeric($permission) AND !is_object($permission)){
            $permission = preg_replace("/\d+/", "*", $permission);
            $permission = PermissionData::where("name", "=", $permission)->first();
        }

        // Get the "super" permission too...
        $super = PermissionData::where("name", "=", "*")->first();

        // Let's check all roles for this permission!
        foreach($this->roles as $r){
            if(($permission && $r->hasPermission($permission)) OR $r->hasPermission($super)){
                return true;
            }
        }

        return false;
    }

    public function hasChildPermission($parent){
        if (is_object($parent)) {
            $parent = $parent->name;
        } elseif(is_numeric($parent)){
            $parent = PermissionData::find($parent);
            $parent = $parent ? $parent->name : "NOTHING-AT-ALL";
        } elseif(!is_numeric($parent)){
            $parent = preg_replace("/\d+/", "*", $parent);
            $parent = PermissionData::where("name", "=", $parent)->first();
            $parent = $parent ? $parent->name : "NOTHING-AT-ALL";
        }

        $childPermissions = PermissionData::where("name", "LIKE", $parent."%")->get();

        // Get the "super" permission too...
        $super = PermissionData::where("name", "=", "*")->first();

        // Let's check all roles for this permission!
        foreach($this->roles as $r){
            if($r->hasPermission($super)){
                return true;
            }

            foreach($childPermissions as $cp){
                if($r->hasPermission($cp)){
                    return true;
                }
            }
        }

        return false;
    }

    public function setPassword($password, $type) {
        if ($this->current_security) {
            $this->current_security->delete();
        }

        // Set a new one!
        $security = new Account\Security();
        $security->account_id = $this->account_id;
        $security->security_id = $type->security_id;
        $security->value = $password;
        $security->save();
    }

    public function resetPassword($admin=false){
        // Now generate a new token for the email.
        $token = SystemToken::generate("mship_account_security_reset", false, $this);

        // Let's send them an email with this information!
        $email = $admin ? "MSHIP_SECURITY_FORGOTTEN_ADMIN" : "MSHIP_SECURITY_FORGOTTEN";
        \Models\Sys\Postmaster\Queue::queue($email, $this, VATUK_ACCOUNT_SYSTEM, ["ip" => array_get($_SERVER, "REMOTE_ADDR", "Unknown"), "token" => $token]);
    }

    public function addEmail($newEmail, $verified = false, $primary = false, $returnID=false) {
        // Check this email doesn't exist for this user already.
        $check = $this->emails()->where("email", "LIKE", $newEmail);
        if ($check->count() < 1) {
            $email = new AccountEmail;
            $email->email = $newEmail;
            if ($verified) {
                $email->verified_at = Carbon::now()->toDateTimeString();
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
        return ($returnID ? $email->account_email_id : $isNewEmail);
    }

    public function addQualification($qualificationType) {
        if (!$qualificationType) {
            return false;
        }

        // Does this rating already exist?
        if ($this->qualifications()->where("qualification_id", "=", $qualificationType->qualification_id)->count() > 0) {
            return false;
        }

        // Let's add it!
        $qual = new AccountQualification;
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
        return (boolean) (self::STATUS_SYSTEM_BANNED & $status);
    }

    public function setIsSystemBannedAttribute($value) {
        if ($value && !$this->is_system_banned) {
            $this->setStatusFlag(self::STATUS_SYSTEM_BANNED);
        } elseif ($this->is_system_banned) {
            $this->unSetStatusFlag(self::STATUS_SYSTEM_BANNED);
        }
    }

    public function getIsNetworkBannedAttribute() {
        $status = $this->attributes['status'];
        return (boolean) ((self::STATUS_NETWORK_SUSPENDED & $status));
    }

    public function setIsNetworkBannedAttribute($value) {
        if ($value && !$this->is_network_banned) {
            $this->setStatusFlag(self::STATUS_NETWORK_SUSPENDED);
        } elseif ($this->is_network_banned) {
            $this->unSetStatusFlag(self::STATUS_NETWORK_SUSPENDED);
        }
    }

    public function getIsBannedAttribute() {
        return $this->is_system_banned OR $this->is_network_banned;
    }

    public function getIsInactive() {
        $status = $this->attributes['status'];
        return (boolean) (self::STATUS_INACTIVE & $status);
    }

    public function setIsInactiveAttribute($value) {
        if ($value && !$this->is_inactive) {
            $this->setStatusFlag(self::STATUS_INACTIVE);
        } elseif ($this->is_inactive) {
            $this->unSetStatusFlag(self::STATUS_INACTIVE);
        }
    }

    public function getIsSystem() {
        $status = $this->attributes['status'];
        return (boolean) (self::STATUS_SYSTEM & $status);
    }

    public function setIsSystemAttribute($value) {
        if ($value && !$this->is_system) {
            $this->setStatusFlag(self::STATUS_SYSTEM);
        } elseif ($this->is_system) {
            $this->unSetStatusFlag(self::STATUS_SYSTEM);
        }
    }

    public function getStatusAttribute() {
        // It's done in a convoluted way, because it's in order of how they should be displayed!
        if ($this->is_system_banned) {
            return Account::getStatusDescription(self::STATUS_SYSTEM_BANNED);
        } elseif ($this->is_network_banned) {
            return Account::getStatusDescription(self::STATUS_NETWORK_SUSPENDED);
        } elseif ($this->is_inactive) {
            return Account::getStatusDescription(self::STATUS_INACTIVE);
        } elseif ($this->is_system) {
            return Account::getStatusDescription(self::STATUS_SYSTEM);
        } else {
            return Account::getStatusDescription(self::STATUS_ACTIVE);
        }
    }

    public function getStatusArrayAttribute() {
        $stati = array();
        if ($this->is_system_banned) {
            $stati[] = getStatusDescription(self::STATUS_SYSTEM_BANNED);
        }

        if ($this->is_network_banned) {
            $stati[] = getStatusDescription(self::STATUS_NETWORK_SUSPENDED);
        }

        if ($this->is_inactive) {
            $stati[] = getStatusDescription(self::STATUS_INACTIVE);
        }

        if ($this->is_system) {
            $stati[] = getStatusDescription(self::STATUS_SYSTEM);
        }

        if (count($stati) < 1) {
            $stati[] = getStatusDescription(self::STATUS_ACTIVE);
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
        return $this->emails()->primary()->first();
    }

    public function getSecondaryEmailAttribute() {
        return $this->emails()->secondary()->get();
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
        if (substr($value, 0, 2) == "mc") {
            $value = "Mc" . ucfirst(substr($value, 2));
        } elseif (substr($value, 0, 3) == "mac") {
            $value = "Mac" . ucfirst(substr($value, 3));
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
        $array['atc_rating'] = $this->qualification_atc;
        $array['atc_rating'] = ($array['atc_rating'] ? $array['atc_rating']->qualification->name_long : "");
        $array['pilot_rating'] = array();
        foreach ($this->qualifications_pilot as $rp) {
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

    public function determineState($region, $division) {
        if ($region == "EUR" AND $division == "GBR") {
            $state = \Enums\Account\State::DIVISION;
        } elseif ($region == "EUR") {
            $state = \Enums\Account\State::REGION;
        } else {
            $state = \Enums\Account\State::INTERNATIONAL;
        }
        if ($this->account_id < 1) {
            print_r($this);
            exit();
        }
        $this->states()->save(new State(array("state" => $state)));
    }

}
