<?php

namespace App\Models\Mship;

use App\Jobs\Mship\Account\SendNewEmailVerificationEmail;
use App\Jobs\Mship\Email\TriggerNewEmailVerificationProcess;
use App\Models\Mship\Account\Ban;
use App\Models\Mship\Account\Email as AccountEmail;
use App\Models\Mship\Account\Note as AccountNoteData;
use App\Models\Mship\Account\Qualification as AccountQualification;
use App\Models\Mship\Account\State;
use App\Models\Mship\Ban\Reason;
use App\Models\Mship\Note\Type;
use App\Models\Mship\Permission as PermissionData;
use App\Models\Mship\Role as RoleData;
use App\Models\Sys\Notification as SysNotification;
use Bus;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

class Account extends \App\Models\aTimelineEntry implements AuthenticatableContract {

    use SoftDeletingTrait, Authenticatable;

    protected $table = 'mship_account';
    protected $primaryKey = 'account_id';
    public $incrementing = false;
    protected $dates = ['last_login', 'joined_at', 'cert_checked_at', 'created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['account_id', 'name_first', 'name_last'];
    protected $attributes = ['name_first' => '', 'name_last' => '', 'status' => self::STATUS_ACTIVE, 'last_login_ip' => '127.0.0.1'];
    protected $doNotTrack = ['session_id', 'cert_checked_at', 'last_login', 'remember_token'];

    const STATUS_ACTIVE = 0; //b"00000';
    //const STATUS_SYSTEM_BANNED = 1; //b"0001"; @deprecated in version 2.2
    //const STATUS_NETWORK_SUSPENDED = 2; //b"0010"; @deprecated in version 2.2
    const STATUS_INACTIVE = 4; //b"0100";
    const STATUS_LOCKED = 8; //b"1000";
    const STATUS_SYSTEM = 8; //b"1000"; // Alias of LOCKED

    public static function eventCreated($model, $extra=null, $data=null){
        parent::eventCreated($model, $extra, $data);

        // Add the user to the default role.
        $defaultRole = RoleData::isDefault()->first();
        if($defaultRole){
            $model->roles()->attach($defaultRole);
        }

        // Generate an email to the user to advise them of their new account at VATUK.
        //Queue::queue('MSHIP_ACCOUNT_CREATED', $model->account_id, VATUK_ACCOUNT_SYSTEM, $model->toArray());
    }

    public static function scopeIsSystem($query){
        return $query->where(\DB::raw(self::STATUS_SYSTEM."&`status`"), '=', self::STATUS_SYSTEM);
    }

    public static function scopeIsNotSystem($query){
        return $query->where(\DB::raw(self::STATUS_SYSTEM."&`status`"), '!=', self::STATUS_SYSTEM);
    }

    public static function scopeWithIp($query, $ip){
        return $query->where('last_login_ip', '=', ip2long($ip));
    }

    public function dataChanges(){
        return $this->morphMany('\App\Models\Sys\Data\Change', 'model')->orderBy('created_at', 'DESC');
    }

    public function emails() {
        return $this->hasMany('\App\Models\Mship\Account\Email', 'account_id', 'account_id');
    }

    public function messageThreads(){
        return $this->belongsToMany(\App\Models\Messages\Thread::class, 'messages_thread_participant', 'account_id', 'thread_id')->withPivot('display_as', 'read_at', 'status')->withTimestamps();
    }

    public function messagePosts(){
        return $this->hasMany(\App\Models\Messages\Thread\Post::class, 'account_id', 'account_id');
    }

    public function bans() {
        return $this->hasMany('\App\Models\Mship\Account\Ban', 'account_id', 'account_id')->orderBy('created_at', 'DESC');
    }

    public function bansAsInstigator() {
        return $this->hasMany('\App\Models\Mship\Account\Ban', 'banned_by', 'account_id')->orderBy('created_at', 'DESC');
    }

    public function notes() {
        return $this->hasMany('\App\Models\Mship\Account\Note', 'account_id', 'account_id')->orderBy('created_at', 'DESC');
    }

    public function noteWriter() {
        return $this->hasMany('\App\Models\Mship\Account\Note', 'writer_id', 'account_id');
    }

    public function tokens() {
        return $this->morphMany('\App\Models\Sys\Token', 'related');
    }

    public function qualifications() {
        return $this->hasMany('\App\Models\Mship\Account\Qualification', 'account_id', 'account_id')->orderBy('created_at', 'DESC')->with('qualification');
    }

    public function roles(){
        return $this->belongsToMany('\App\Models\Mship\Role', 'mship_account_role')->with('permissions')->withTimestamps();
    }

    public function states() {
        return $this->hasMany('\App\Models\Mship\Account\State', 'account_id', 'account_id')->orderBy('created_at', 'DESC');
    }

    public function ssoEmails() {
        return $this->hasMany('\App\Models\Sso\Email', 'account_id', 'account_id');
    }

    public function ssoTokens() {
        return $this->hasMany('\App\Models\Sso\Token', 'account_id', 'account_id');
    }

    public function security() {
        return $this->hasMany('\App\Models\Mship\Account\Security', 'account_id', 'account_id')->orderBy('created_at', 'DESC');
    }

    public function teamspeakAliases() {
        return $this->hasMany('\App\Models\Teamspeak\Alias', 'account_id', 'account_id');
    }

    public function teamspeakRegistrations() {
        return $this->hasMany('\App\Models\Teamspeak\Registration', 'account_id', 'account_id');
    }

    public function readNotifications(){
        return $this->belongsToMany('\App\Models\Sys\Notification', 'sys_notification_read', 'account_id', 'notification_id')->orderBy('status', 'DESC')->orderBy('effective_at', 'DESC')->withTimestamps();
    }

    public function getUnreadNotificationsAttribute(){
        // Get all read notifications
        $readNotifications = $this->readNotifications;

        // Get all notifications
        $allNotifications = SysNotification::published()
                                           //->since($this->created_at) TODO: Add back AFTER we've launched new T&Cs.
                                           ->orderBy('status', 'DESC')
                                           ->orderBy('effective_at', 'DESC')
                                           ->get();

        // The difference between the two MUST be the ones that are unread, right?
        return $allNotifications->diff($readNotifications);
    }

    public function getHasUnreadNotificationsAttribute(){
        return $this->unreadNotifications->count() > 0;
    }

    public function getHasUnreadImportantNotificationsAttribute(){
        $unreadNotifications = $this->unreadNotifications->filter(function($notice){
            return $notice->status == SysNotification::STATUS_IMPORTANT;
        });

        return $unreadNotifications->count() > 0;
    }

    public function getHasUnreadMustAcknowledgeNotificationsAttribute(){
        $unreadNotifications = $this->unreadNotifications->filter(function($notice){
            return $notice->status == SysNotification::STATUS_MUST_ACKNOWLEDGE;
        });

        return $unreadNotifications->count() > 0;
    }

    public function getQualificationAtcAttribute() {
        return $this->qualifications->filter(function($qual){
            return $qual->qualification->type == 'atc';
        })->first();
    }

    public function getQualificationsAtcAttribute() {
        return $this->qualifications->filter(function($qual){
            return $qual->qualification->type == 'atc';
        });
    }

    public function getQualificationsAtcTrainingAttribute() {
        return $this->qualifications->filter(function($qual){
            return $qual->qualification->type == 'training_atc';
        });
    }

    public function getQualificationsPilotAttribute() {
        return $this->qualifications->filter(function($qual){
            return $qual->qualification->type == 'pilot';
        });
    }

    public function getQualificationsPilotStringAttribute(){
        $output = '';
        foreach ($this->qualifications_pilot as $p) {
            $output.= $p->qualification->code . ', ';
        }
        if($output == ''){
            $output = 'None';
        }
        return rtrim($output, ', ');
    }

    public function getQualificationsPilotTrainingAttribute() {
        return $this->qualifications->filter(function($qual){
            return $qual->qualification->type == 'training_pilot';
        });
    }

    public function getQualificationsAdminAttribute() {
        return $this->qualifications->filter(function($qual){
            return $qual->qualification->type == 'admin';
        });
    }

    public function isState($search) {
        return !$this->states->filter(function($state) use ($search){
            return $state->state == $search;
        })->isEmpty();
    }

    public function getCurrentStateAttribute() {
        return $this->states->first();
    }

    public function getAllStatesAttribute(){
        $return = array();

        foreach($this->states as $state){
            $key = strtolower(State::getStateKeyFromValue($state->state));
            $return[$key] = 1;
            $return[$key.'_date'] = $state->created_at->toDateTimeString();
        }
        return $return;
    }

    public function getPrimaryStateAttribute() {
        return $this->current_state;
    }

    public function getCurrentSecurityAttribute() {
        return $this->security->first();
    }
    public function hasPermission($permission){
        if(is_numeric($permission)){
            $permission = PermissionData::find($permission);
            $permission = $permission ? $permission->name : 'NOTHING';
        } elseif(is_object($permission)){
            $permission = $permission->name;
        } else {
            $permission = preg_replace('/\d+/', '*', $permission);
        }

        // Let's check all roles for this permission!
        foreach($this->roles as $r){
            if($r->hasPermission($permission)){
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
            $parent = $parent ? $parent->name : 'NOTHING-AT-ALL';
        } elseif(!is_numeric($parent)){
            $parent = preg_replace('/\d+/', '*', $parent);
        }

        // Let's check all roles for this permission!
        foreach($this->roles as $r){
            if($r->hasPermission($parent)){
                return true;
            }
        }

        return false;
    }

    public function setPassword($password, $type, $temp = FALSE) {
        if ($this->current_security) {
            $this->current_security->delete();
        }

        // Set a new one!
        $security = new Account\Security();
        $security->account_id = $this->account_id;
        $security->security_id = $type->security_id;
        $security->value = $password;
        if ($temp) $security->expires_at = Carbon::now()->toDateTimeString();
        $security->save();
    }

    public function addEmail($newEmail, $verified = false, $primary = false, $returnID=false) {
        // Check this email doesn't exist for this user already.
        $check = $this->emails->filter(function($email) use($newEmail){
            return strcasecmp($email->email, $newEmail) == 0;
        })->first();

        if (!$check OR !$check->exists) {
            $email = new AccountEmail;
            $email->email = $newEmail;
            if ($verified) {
                $email->verified_at = Carbon::now();
            }
            $this->emails()->save($email);

            $isNewEmail = true;

            // Verify if it's not primary.
            if(!$primary){
                Bus::dispatch(new TriggerNewEmailVerificationProcess($email));
            }
        } else {
            $email = $check;
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
        $check = $this->qualifications->filter(function($qual) use($qualificationType){
            return $qual->qualification_id == $qualificationType->qualification_id;
        })->count() > 0;
        if ($check) {
            return false;
        }

        // Let's add it!
        $qual = new AccountQualification;
        $qual->qualification_id = $qualificationType->qualification_id;
        $this->qualifications()->save($qual);

        return true;
    }

    public function addBan(Reason $banReason, $banExtraReason=null, $banNote=null, $writerId=null, $type=Ban::TYPE_LOCAL){
        if($writerId == null){
            $writerId = VATUK_ACCOUNT_SYSTEM;
        } elseif(is_object($writerId)){
            $writerId = $writerId->getKey();
        }

        // Attach the note.
        $note = $this->addNote(Type::isShortCode('discipline')->first(), $banNote, $writerId);

        // Make a ban.
        $ban = new Ban();
        $ban->account_id = $this->account_id;
        $ban->banned_by = $writerId;
        $ban->type = $type;
        $ban->reason_id = $banReason->ban_reason_id;
        $ban->reason_extra = $banExtraReason;
        $ban->period_start = \Carbon\Carbon::now()->second(0);
        $ban->period_finish = \Carbon\Carbon::now()->addHours($banReason->period_hours)->second(0);
        $ban->save();

        $ban->notes()->save($note);

        return $ban;
    }

    public function addNote($noteType, $noteContent, $writer=null, $attachment=null){
        if(is_object($noteType)){
            $noteType = $noteType->getKey();
        }

        if($writer == null){
            $writer = VATUK_ACCOUNT_SYSTEM;
        } elseif(is_object($writer)){
            $writer = $writer->getKey();
        }

        $note = new AccountNoteData();
        $note->account_id = $this->account_id;
        $note->writer_id = $writer;
        $note->note_type_id = $noteType;
        $note->content = $noteContent;
        $note->save();

        if(!is_null($attachment)){
            $note->attachment()->save($attachment);
        }

        return $note;
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
        $bans = $this->bans()->isActive()->isLocal();
        return ($bans->count() > 0);
    }

    public function getSystemBanAttribute(){
        $bans = $this->bans()->isActive()->isLocal();
        return $bans->first();
    }

    public function getIsNetworkBannedAttribute() {
        $bans = $this->bans()->isActive()->isNetwork();
        return ($bans->count() > 0);
    }

    public function getNetworkBanAttribute(){
        $bans = $this->bans()->isActive()->isNetwork();
        return $bans->first();
    }

    public function getIsBannedAttribute() {
        return ($this->is_system_banned || $this->is_network_banned);
    }

    public function getIsInactiveAttribute() {
        $status = $this->attributes['status'];
        return (boolean) (self::STATUS_INACTIVE & $status);
    }

    public function setIsInactiveAttribute($value) {
        if ($value && !$this->is_inactive) {
            $this->setStatusFlag(self::STATUS_INACTIVE);
        } elseif (!$value && $this->is_inactive) {
            $this->unSetStatusFlag(self::STATUS_INACTIVE);
        }
    }

    public function getIsSystemAttribute() {
        $status = $this->attributes['status'];
        return (boolean) (self::STATUS_SYSTEM & $status);
    }

    public function setIsSystemAttribute($value) {
        if ($value && !$this->is_system) {
            $this->setStatusFlag(self::STATUS_SYSTEM);
        } elseif (!$value && $this->is_system) {
            $this->unSetStatusFlag(self::STATUS_SYSTEM);
        }
    }

    public function getStatusStringAttribute() {
        // It's done in a convoluted way, because it's in order of how they should be displayed!
        if ($this->is_system_banned) {
            return trans('mship.account.status.ban.local');
        } elseif ($this->is_network_banned) {
            return trans('mship.account.status.ban.network');
        } elseif ($this->is_inactive) {
            return trans('mship.account.status.inactive');
        } elseif ($this->is_system) {
            return trans('mship.account.status.system');
        } else {
            return trans('mship.account.status.active');
        }
    }

    public function getStatusArrayAttribute() {
        $stati = array();
        if ($this->is_system_banned) {
            $stati[] = trans('mship.account.status.ban.local');
        }

        if ($this->is_network_banned) {
            $stati[] = trans('mship.account.status.ban.network');
        }

        if ($this->is_inactive) {
            $stati[] = trans('mship.account.status.inactive');
        }

        if ($this->is_system) {
            $stati[] = trans('mship.account.status.system');
        }

        if (count($stati) < 1) {
            $stati[] = trans('mship.account.status.active');
        }
        return $stati;
    }

    public function getLastLoginIpAttribute() {
        return long2ip($this->attributes['last_login_ip']);
    }

    public function setLastLoginIpAttribute($value) {
        $this->attributes['last_login_ip'] = ip2long($value);
    }

    public function getPrimaryEmailAttribute() {
        return $this->emails->filter(function($email){
            return $email->is_primary == 1;
        })->first();
    }

    public function getSecondaryEmailAttribute() {
        return $this->emails->filter(function($email){
            return !$email->is_primary;
        });
    }

    public function getSecondaryEmailVerifiedAttribute() {
        return $this->emails->filter(function($email){
            return !$email->is_primary && $email->verified_at != null;
        });
    }

    public function setNameFirstAttribute($value) {
        $value = trim($value);

        if ($value == strtoupper($value) || $value == strtolower($value)) {
            $value = ucwords(strtolower($value));
        }

        $this->attributes['name_first'] = $value;
    }

    public function setNameLastAttribute($value) {
        $value = trim($value);

        if ($value == strtoupper($value) || $value == strtolower($value)) {
            $value = ucwords(strtolower($value));
        }

        $this->attributes['name_last'] = $value;
    }

    public function getNameAttribute() {
        return $this->attributes['name_first'] . ' ' . $this->attributes['name_last'];
    }

    public function renewSalt() {
        $salt = md5(uniqid() . md5(time()));
        $salt = substr($salt, 0, 20);
        $this->salt = $salt;
        $this->save();
        return $salt;
    }

    public function getDisplayValueAttribute() {
        return $this->name . ' (' . $this->getKey() . ')';
    }

    public function toArray() {
        $array = parent::toArray();
        $array['name'] = $this->name;
        $array['email'] = $this->primary_email ? $this->primary_email->email : new Account\Email();
        $array['atc_rating'] = $this->qualification_atc;
        $array['atc_rating'] = ($array['atc_rating'] ? $array['atc_rating']->qualification->name_long : '');
        $array['pilot_rating'] = array();
        foreach ($this->qualifications_pilot as $rp) {
            $array['pilot_rating'][] = $rp->qualification->code;
        }
        $array['pilot_rating'] = implode(', ', $array['pilot_rating']);
        return $array;
    }

    public function determineState($region, $division) {
        if ($region == 'EUR' AND $division == 'GBR') {
            $state = \App\Models\Mship\Account\State::STATE_DIVISION;
        } elseif ($region == 'EUR') {
            $state = \App\Models\Mship\Account\State::STATE_REGION;
        } else {
            $state = \App\Models\Mship\Account\State::STATE_INTERNATIONAL;
        }
        $this->states()->save(new Account\State(array('state' => $state)));
    }

    public function getNewRegistrationAttribute() {
        return $this->teamspeakRegistrations->filter(function($reg) {
            return $reg->status == 'new';
        })->first();
    }

    public function getConfirmedRegistrationsAttribute() {
        return $this->teamspeakRegistrations->filter(function($reg) {
            return $reg->status != 'new';
        });
    }

    public function isValidTeamspeakAlias($tAlias)
    {
        foreach ($this->teamspeakAliases as $rAlias) {
            if (strcasecmp($rAlias->display_name, $tAlias) == 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns what the session timeout for this user should be, in minutes.
     *
     * @return int returns 0 if there is no timeout, else returns the timeout in minutes
     */
    public function getSessionTimeoutAttribute()
    {
        $timeout = 0;
        foreach ($this->roles->filter([RoleData::class, 'hasTimeout']) as $role) {
            if ($timeout == 0 || $role->session_timeout < $timeout) {
                $timeout = $role->session_timeout;
            }
        }

        return $timeout;
    }
}
