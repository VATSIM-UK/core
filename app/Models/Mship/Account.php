<?php

namespace App\Models\Mship;

use App\Exceptions\Mship\DuplicateEmailException;
use App\Exceptions\Mship\DuplicateQualificationException;
use App\Http\Controllers\Mship\Security;
use App\Jobs\Mship\Email\SendNewEmailVerificationEmail;
use App\Jobs\Mship\Email\TriggerNewEmailVerificationProcess;
use App\Models\Mship\Account\Ban;
use App\Models\Mship\Account\Email as AccountEmail;
use App\Models\Mship\Account\Email;
use App\Models\Mship\Account\Note as AccountNoteData;
use App\Models\Mship\Account\Qualification as AccountQualification;
use App\Models\Mship\Account\State;
use App\Models\Mship\Ban\Reason;
use App\Models\Mship\Note\Type;
use App\Models\Mship\Permission as PermissionData;
use App\Models\Mship\Qualification;
use App\Models\Mship\Role as RoleData;
use App\Models\Sys\Notification as SysNotification;
use App\Modules\Visittransfer\Exceptions\Application\DuplicateApplicationException;
use App\Modules\Visittransfer\Models\Application;
use App\Traits\RecordsActivity as RecordsActivityTrait;
use Bus;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;

/**
 * App\Models\Mship\Account
 *
 * @property integer $id
 * @property string $slack_id
 * @property string $name_first
 * @property string $name_last
 * @property string $nickname
 * @property string $email
 * @property string $password
 * @property \Carbon\Carbon $password_set_at
 * @property \Carbon\Carbon $password_expires_at
 * @property string $session_id
 * @property \Carbon\Carbon $last_login
 * @property integer $last_login_ip
 * @property string $remember_token
 * @property string $gender
 * @property string $experience
 * @property integer $age
 * @property integer $status
 * @property boolean $is_invisible
 * @property boolean $debug
 * @property \Carbon\Carbon $joined_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $cert_checked_at
 * @property \Carbon\Carbon $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Email[] $secondaryEmails
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Messages\Thread\Post[] $messagePosts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Ban[] $bans
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Ban[] $bansAsInstigator
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Note[] $notes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Note[] $noteWriter
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Token[] $tokens
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Activity[] $activityRecent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Qualification[] $qualifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Role[] $roles
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\State[] $states
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sso\Email[] $ssoEmails
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sso\Token[] $ssoTokens
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TeamSpeak\Registration[] $teamspeakRegistrations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Notification[] $readNotifications
 * @property-read mixed $unread_notifications
 * @property-read mixed $unread_must_acknowledge_notifications
 * @property-read mixed $has_unread_notifications
 * @property-read mixed $has_unread_important_notifications
 * @property-read mixed $has_unread_must_acknowledge_notifications
 * @property-read mixed $unread_must_acknowledge_time_elapsed
 * @property-read mixed $active_qualifications
 * @property-read mixed $qualification_atc
 * @property-read mixed $qualifications_atc
 * @property-read mixed $qualifications_atc_training
 * @property-read mixed $qualifications_pilot
 * @property-read mixed $qualifications_pilot_string
 * @property-read mixed $qualifications_pilot_training
 * @property-read mixed $qualifications_admin
 * @property-read mixed $current_state
 * @property-read mixed $all_states
 * @property-read mixed $password_lifetime
 * @property-read mixed $mandatory_password
 * @property mixed $is_inactive
 * @property mixed $is_system
 * @property-read mixed $is_system_banned
 * @property-read mixed $system_ban
 * @property-read mixed $is_network_banned
 * @property-read mixed $network_ban
 * @property-read mixed $is_banned
 * @property-read mixed $status_string
 * @property-read mixed $status_array
 * @property-read mixed $verified_secondary_emails
 * @property-read mixed $real_name
 * @property-read mixed $name
 * @property-read mixed $full_name
 * @property-read mixed $display_value
 * @property-read mixed $new_ts_registration
 * @property-read mixed $session_timeout
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereSlackId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereNameFirst($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereNameLast($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereNickname($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account wherePassword($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account wherePasswordSetAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account wherePasswordExpiresAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereSessionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereLastLogin($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereLastLoginIp($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereRememberToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereGender($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereExperience($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereAge($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereIsInvisible($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereDebug($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereJoinedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereCertCheckedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account isSystem()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account isNotSystem()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account withIp($ip)
 * @mixin \Eloquent
 */
class Account extends \App\Models\aModel implements AuthenticatableContract
{
    use SoftDeletingTrait, Authenticatable, RecordsActivityTrait;

    protected $table = 'mship_account';
    public $incrementing = false;
    protected $dates = [
        'last_login',
        'joined_at',
        'cert_checked_at',
        'created_at',
        'updated_at',
        'deleted_at',
        "password_set_at",
        "password_expires_at",
    ];
    protected $fillable = [
        'id',
        'name_first',
        'name_last',
        'email',
        "password",
        "password_set_at",
        "password_expires_at",
    ];
    protected $attributes = [
        'name_first'    => '',
        'name_last'     => '',
        'status'        => self::STATUS_ACTIVE,
        'last_login_ip' => '127.0.0.1',
    ];
    protected $doNotTrack = ['session_id', 'cert_checked_at', 'last_login', 'remember_token'];

    // Suggested values in version 2.2.4
//    const STATUS_ACTIVE = 1; // b"000001"
//    const STATUS_LOCKED = 2; // b"000100"
//    const STATUS_SYSTEM = 4; // b"001000";
//    const STATUS_PASSWORD_EXPIRED = 8; // b"000010"

    const STATUS_ACTIVE = 0; //b"00000';
    //const STATUS_SYSTEM_BANNED = 1; //b"0001"; @deprecated in version 2.2
    //const STATUS_NETWORK_SUSPENDED = 2; //b"0010"; @deprecated in version 2.2
    const STATUS_INACTIVE = 4; //b"0100";
    const STATUS_LOCKED = 8; //b"1000";
    const STATUS_SYSTEM = 8; //b"1000"; // Alias of LOCKED

    public static function eventCreated($model, $extra = null, $data = null)
    {
        parent::eventCreated($model, $extra, $data);

        // Add the user to the default role.
        $defaultRole = RoleData::isDefault()->first();
        if ($defaultRole) {
            $model->roles()->attach($defaultRole);
        }
    }

    /**
     * Find and fetch the user with the given slack ID.
     *
     * @param string $slackId Slack ID to locate user by.
     *
     * @return \Illuminate\Database\Eloquent\Model|mixed|null|static
     */
    public static function findWithSlackId($slackId)
    {
        return Account::where("slack_id", "=", $slackId)->first();
    }

    public static function scopeIsSystem($query)
    {
        return $query->where(\DB::raw(self::STATUS_SYSTEM . "&`status`"), '=', self::STATUS_SYSTEM);
    }

    public static function scopeIsNotSystem($query)
    {
        return $query->where(\DB::raw(self::STATUS_SYSTEM . "&`status`"), '!=', self::STATUS_SYSTEM);
    }

    public static function scopeWithIp($query, $ip)
    {
        return $query->where('last_login_ip', '=', ip2long($ip));
    }

    /**
     * Fetch all related visiting/transfer applications
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visitTransferApplications(){
        return $this->hasMany(\App\Modules\Visittransfer\Models\Application::class)->orderBy("submitted_at", "DESC");
    }

    public function visitApplications(){
        return $this->visitTransferApplications()->where("type", "=", Application::TYPE_VISIT);
    }

    public function transferApplications(){
        return $this->visitTransferApplications()->where("type", "=", Application::TYPE_TRANSFER);
    }

    public function visitTransferCurrent(){
        return $this->visitTransferApplications()->latest()->first();
    }

    public function createVisitingTransferApplication(array $attributes){
        $this->guardAgainstDivisionMemberVisitingTransferApplication();
        $this->guardAgainstDuplicateVisitingTransferApplications();

        return $this->visitTransferApplications()->create($attributes);
    }

    private function guardAgainstDivisionMemberVisitingTransferApplication()
    {
        if($this->hasState(\App\Models\Mship\Account\State::STATE_DIVISION)){
            throw new \App\Modules\Visittransfer\Exceptions\Application\AlreadyADivisionMemberException($this);
        }
    }

    private function guardAgainstDuplicateVisitingTransferApplications(){
        if($this->hasOpenVisitingTransferApplication()){
            throw new DuplicateApplicationException($this);
        }
    }

    public function hasOpenVisitingTransferApplication(){
        return $this->visitTransferApplications->contains(function($key, $application){
            return in_array($application->status, \App\Modules\Visittransfer\Models\Application::$APPLICATION_IS_CONSIDERED_OPEN);
        });
    }

    public function visitTransferReferee(){
        return $this->hasMany(\App\Modules\Visittransfer\Models\Reference::class);
    }

    /**
     * Fetch all related secondary emails.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function secondaryEmails()
    {
        return $this->hasMany(\App\Models\Mship\Account\Email::class, 'account_id');
    }

    public function dataChanges()
    {
        return $this->morphMany(\App\Models\Sys\Data\Change::class, 'model')
                    ->orderBy('created_at', 'DESC');
    }

    public function messageThreads()
    {
        return $this->belongsToMany(\App\Models\Messages\Thread::class, 'messages_thread_participant', 'thread_id')
                    ->withPivot('display_as', 'read_at', 'status')->withTimestamps();
    }

    public function messagePosts()
    {
        return $this->hasMany(\App\Models\Messages\Thread\Post::class, 'account_id');
    }

    public function bans()
    {
        return $this->hasMany(\App\Models\Mship\Account\Ban::class, 'account_id')->orderBy('created_at',
            'DESC');
    }

    public function bansAsInstigator()
    {
        return $this->hasMany(\App\Models\Mship\Account\Ban::class, 'banned_by')
                    ->orderBy('created_at', 'DESC');
    }

    public function notes()
    {
        return $this->hasMany(\App\Models\Mship\Account\Note::class, 'account_id')
                    ->orderBy('created_at', 'DESC');
    }

    public function noteWriter()
    {
        return $this->hasMany(\App\Models\Mship\Account\Note::class, 'writer_id');
    }

    public function tokens()
    {
        return $this->morphMany(\App\Models\Sys\Token::class, 'related');
    }

    public function activityRecent()
    {
        return $this->hasMany(\App\Models\Sys\Activity::class, "actor_id");
    }

    public function qualifications()
    {
        return $this->belongsToMany(Qualification::class, "mship_account_qualification", "account_id",
            "qualification_id")
                    ->withTimestamps();
    }

    public function roles()
    {
        return $this->belongsToMany(\App\Models\Mship\Role::class, 'mship_account_role')
                    ->with('permissions')
                    ->withTimestamps();
    }

    /**
     * Determine if the given role is attached to this account.
     *
     * @param Role $role The role to check.
     *
     * @return bool
     */
    public function hasRole(Role $role)
    {
        return $this->roles->contains($role->id);
    }

    /**
     * Attach a role to this account.
     *
     * @param Role $role The role to attach to the account.
     *
     * @return mixed
     */
    public function addRole(Role $role)
    {
        if ($this->hasRole($role)) {
            throw new \App\Exceptions\Mship\DuplicateRoleException($role);
        }

        return $this->roles()->attach($role->id);
    }

    /**
     * Detach a role from this account.
     *
     * @param Role $role The role to remove from the account.
     *
     * @return bool
     */
    public function removeRole(Role $role)
    {
        if (!$this->hasRole($role)) {
            return true;
        }

        return $this->roles()->detach($role->id);
    }

    /**
     * Return all related states for this account.
     *
     * @return mixed
     */
    public function states()
    {
        return $this->hasMany(\App\Models\Mship\Account\State::class)
                    ->orderBy('created_at', 'DESC');
    }

    public function ssoEmails()
    {
        return $this->hasMany(\App\Models\Sso\Email::class, 'account_id');
    }

    public function ssoTokens()
    {
        return $this->hasMany(\App\Models\Sso\Token::class, 'account_id');
    }

    public function teamspeakRegistrations()
    {
        return $this->hasMany(\App\Models\TeamSpeak\Registration::class, 'account_id');
    }

    public function readNotifications()
    {
        return $this->belongsToMany(\App\Models\Sys\Notification::class, 'sys_notification_read', 'account_id',
            'notification_id')
                    ->orderBy('status', 'DESC')
                    ->orderBy('effective_at', 'DESC')
                    ->withTimestamps();
    }

    public function getUnreadNotificationsAttribute()
    {
        // Get all read notifications
        $readNotifications = $this->readNotifications;

        // Get all notifications
        $allNotifications = SysNotification::published()
                                           ->orderBy('status', 'DESC')
                                           ->orderBy('effective_at', 'DESC')
                                           ->get();

        // The difference between the two MUST be the ones that are unread, right?
        return $allNotifications->diff($readNotifications);
    }
    
    public function getUnreadMustAcknowledgeNotificationsAttribute()
    {
        return $this->unread_notifications->filter(function ($notification) {
            return $notification->status === SysNotification::STATUS_MUST_ACKNOWLEDGE;
        });
    }

    public function getHasUnreadNotificationsAttribute()
    {
        return $this->unreadNotifications->count() > 0;
    }

    public function getHasUnreadImportantNotificationsAttribute()
    {
        $unreadNotifications = $this->unreadNotifications->filter(function ($notice) {
            return $notice->status == SysNotification::STATUS_IMPORTANT;
        });

        return $unreadNotifications->count() > 0;
    }

    public function getHasUnreadMustAcknowledgeNotificationsAttribute()
    {
        $unreadNotifications = $this->unreadNotifications->filter(function ($notice) {
            return $notice->status == SysNotification::STATUS_MUST_ACKNOWLEDGE;
        });

        return $unreadNotifications->count() > 0;
    }

    /**
     * Calculates the amount of time that has lapsed since the notification became effective.
     * 
     * @return int Period the notification has been effective for, in hours.
     */
    public function getUnreadMustAcknowledgeTimeElapsedAttribute()
    {
        if ($this->has_unread_must_acknowledge_notifications) {
            return $this->unread_must_acknowledge_notifications
                ->sortBy('effective_at')
                ->first()
                ->effective_at
                ->diffInHours(Carbon::now(), true);
        } else {
            return 0;
        }
    }
    
    public function getActiveQualificationsAttribute()
    {
        $this->load('qualifications');
        
        return $this->qualifications_pilot
            ->merge($this->qualifications_atc_training)
            ->merge($this->qualifications_pilot_training)
            ->merge($this->qualifications_admin)
            ->push($this->qualification_atc);
    }
    
    public function getQualificationAtcAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == 'atc';
        })->sortByDesc(function ($qualification, $key) {
            return $qualification->pivot->created_at;
        })->first();
    }

    public function getQualificationsAtcAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == 'atc';
        });
    }

    public function getQualificationsAtcTrainingAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == 'training_atc';
        });
    }

    public function getQualificationsPilotAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == 'pilot';
        });
    }

    public function getQualificationsPilotStringAttribute()
    {
        $output = '';
        foreach ($this->qualifications_pilot as $p) {
            $output .= $p->code . ', ';
        }
        if ($output == '') {
            $output = 'None';
        }

        return rtrim($output, ', ');
    }

    public function getQualificationsPilotTrainingAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == 'training_pilot';
        });
    }

    public function getQualificationsAdminAttribute()
    {
        return $this->qualifications->filter(function ($qual) {
            return $qual->type == 'admin';
        });
    }

    /**
     * Check whether the user has the given state presently.
     *
     * @param int $search The given state to check if the account has.
     *
     * @return bool
     */
    public function hasState($search)
    {
        return !$this->states->filter(function ($state) use ($search) {
            return $state->state == $search;
        })->isEmpty();
    }

    /**
     * Set the account's current state to the given value.
     *
     * @param int $state The state to set.
     *
     * @return mixed
     * @throws \App\Exceptions\Mship\DuplicateStateException
     */
    public function setState($state)
    {
        if ($this->hasState($state)) {
            throw new \App\Exceptions\Mship\DuplicateStateException($state);
        }

        if ($this->currentState) {
            $this->currentState->delete();
        }

        return $this->states()->create([
            "state" => $state,
        ]);
    }

    /**
     * Laravel magic-getter - return the current state.
     *
     * @return mixed
     */
    public function getCurrentStateAttribute()
    {
        return $this->states->first();
    }

    public function getAllStatesAttribute()
    {
        $return = collect();

        foreach ($this->states as $state) {
            $key = strtolower(State::getStateKeyFromValue($state->state));
            $return->put($key, 1);
            $return->put($key . '_date', $state->created_at->toDateTimeString());
        }

        return collect($return);
    }

    public function hasPermission($permission)
    {
        if (is_numeric($permission)) {
            $permission = PermissionData::find($permission);
            $permission = $permission ? $permission->name : 'NOTHING';
        } elseif (is_object($permission)) {
            $permission = $permission->name;
        } else {
            $permission = preg_replace('/\d+/', '*', $permission);
        }

        // Let's check all roles for this permission!
        foreach ($this->roles as $r) {
            if ($r->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasChildPermission($parent)
    {
        if (is_object($parent)) {
            $parent = $parent->name;
        } elseif (is_numeric($parent)) {
            $parent = PermissionData::find($parent);
            $parent = $parent ? $parent->name : 'NOTHING-AT-ALL';
        } elseif (!is_numeric($parent)) {
            $parent = preg_replace('/\d+/', '*', $parent);
        }

        // Let's check all roles for this permission!
        $hasPermission = $this->roles->filter(function ($role) use ($parent) {
                return $role->hasPermission($parent);
            })->count() > 0;

        return $hasPermission;
    }

    public function verifyPassword($password)
    {
        if ($this->password == sha1(sha1($password))) {
            $this->password = $password;
            $this->save();
        }

        return \Hash::check($password, $this->password);
    }

    /**
     * Set the password attribute correctly.
     *
     * Will hash the password, or set it as null if required.
     *
     * @param null|string $password The password value to set.
     */
    public function setPasswordAttribute($password)
    {
        if ($password == null) {
            $this->attributes[ 'password' ] = null;

            return;
        }

        $this->attributes[ 'password' ] = \Hash::make($password);
    }

    /**
     * Determine whether the current account has a password set.
     *
     * @return boolean
     */
    public function hasPassword()
    {
        return $this->password !== null;
    }

    /**
     * Determine whether the current password has expired.
     *
     * @return boolean
     */
    public function hasPasswordExpired()
    {
        if (!$this->hasPassword()) {
            return false;
        }

        if ($this->password_expires_at === null) {
            return false;
        }

        return $this->password_expires_at->isPast();
    }

    /**
     * Get password lifetime attribute from the member's roles.
     *
     * @return integer
     */
    public function getPasswordLifetimeAttribute()
    {
        return $this->roles->filter(function ($role) {
            return $role->hasPasswordLifetime();
        })->pluck("password_lifetime")
                           ->min();
    }

    /**
     * Determine whether this account's password is mandatory.
     *
     * @return bool
     */
    public function getMandatoryPasswordAttribute()
    {
        return $this->roles->filter(function ($role) {
            return $role->hasMandatoryPassword();
        })->count() > 0;
    }

    /**
     * Calculate the password expiry for this account.
     *
     * @param bool|false $temporary Should we treat the password as temporary?
     *
     * @return null|\Carbon\Carbon
     */
    public function calculatePasswordExpiry($temporary = false)
    {
        if ($temporary) {
            return Carbon::now();
        }

        if ($this->password_lifetime > 0) {
            return Carbon::now()->addDays($this->password_lifetime);
        }

        return null;
    }

    /**
     * Set the user's password.
     *
     * @param            $password The password string.
     * @param bool|false $temporary Will only be a temporary password
     */
    public function setPassword($password, $temporary = false)
    {
        if (\Hash::check($password, $this->password)) {
            throw new \App\Exceptions\Mship\DuplicatePasswordException;
        }

        return $this->fill([
            "password"            => $password,
            "password_set_at"     => Carbon::now(),
            "password_expires_at" => $this->calculatePasswordExpiry($temporary),
        ])->save();
    }

    /**
     * Remove a member's current password.
     *
     * @return bool
     */
    public function removePassword()
    {
        $this->fill([
            "password"            => null,
            "password_set_at"     => null,
            "password_expires_at" => null,
        ])->save();
    }

    /**
     * Determine if the current account has the given email attached to it.
     *
     * @param string $email The email to check is attached to this account.
     * @param bool   $checkPrimary Whether to also check the primary email address.
     *
     * @return bool
     */
    public function hasEmail($email, $checkPrimary = true)
    {
        if ($checkPrimary && strcasecmp($email, $this->email) == 0) {
            return true;
        }

        $check = $this->secondaryEmails->filter(function ($e) use ($email) {
            return strcasecmp($e->email, $email) == 0;
        })->count();

        return $check > 0;
    }

    /**
     * Set an account's primary email to the one given.
     *
     * If the primary email exists as a secondary, it'll be deleted.
     *
     * @param string $primaryEmail The new primary email for the account.
     * @return boolean
     */
    public function setEmail($primaryEmail)
    {
        $checkPrimaryEmail = false;
        if ($this->hasEmail($primaryEmail, $checkPrimaryEmail)) {

            $secondaryEmail = $this->secondaryEmails->filter(function ($secondaryEmail) use ($primaryEmail) {
                return strcasecmp($secondaryEmail->email, $primaryEmail) == 0;
            })->first();

            $secondaryEmail->delete();
        }

        $this->attributes[ 'email' ] = strtolower($primaryEmail);

        return $this->save();
    }

    /**
     * Laravel magic setter - calls the setEmail method and instantly saves.
     *
     * @param string $email
     * @return boolean
     */
    public function setEmailAttribute($email)
    {
        return $this->setEmail($email);
    }

    /**
     * Attach a new secondary email to this user account.
     *
     * @param String $newEmail The new email address to add to this account.
     * @param bool   $verified Set to TRUE if the email should be automatically verified.
     *
     * @return \Illuminate\Database\Eloquent\Model|int
     * @throws \App\Exceptions\Mship\DuplicateEmailException
     */
    public function addSecondaryEmail($newEmail, $verified = false)
    {
        if ($this->hasEmail($newEmail)) {
            throw new DuplicateEmailException($newEmail);
        }

        $newSecondaryEmail              = new AccountEmail(["email" => $newEmail]);
        $newSecondaryEmail->verified_at = ($verified ? Carbon::now() : null);

        return $this->secondaryEmails()->save($newSecondaryEmail);
    }

    /**
     * Determine if the given qualification exists on the member account.
     *
     * @param Qualification $qualification
     *
     * @return bool
     */
    public function hasQualification(Qualification $qualification)
    {
        return $this->qualifications->filter(function ($q) use ($qualification) {
            return $q->id == $qualification->id;
        })->count() > 0;
    }

    /**
     * Add a qualification to the current member account.
     *
     * @param Qualification $qualification
     *
     * @return bool
     * @throws DuplicateQualificationException
     */
    public function addQualification(Qualification $qualification)
    {
        if ($this->hasQualification($qualification)) {
            throw new DuplicateQualificationException($qualification);
        }

        $this->qualifications()->attach($qualification);

        $this->touch();

        return true;
    }

    public function addBan(
        Reason $banReason,
        $banExtraReason = null,
        $banNote = null,
        $writerId = null,
        $type = Ban::TYPE_LOCAL
    ) {
        if ($writerId == null) {
            $writerId = VATUK_ACCOUNT_SYSTEM;
        } elseif (is_object($writerId)) {
            $writerId = $writerId->getKey();
        }

        // Attach the note.
        $note = $this->addNote(Type::isShortCode('discipline')->first(), $banNote, $writerId);

        // Make a ban.
        $ban                = new Ban();
        $ban->account_id    = $this->id;
        $ban->banned_by     = $writerId;
        $ban->type          = $type;
        $ban->reason_id     = $banReason->id;
        $ban->reason_extra  = $banExtraReason;
        $ban->period_start  = Carbon::now()->second(0);
        $ban->period_finish = Carbon::now()->addHours($banReason->period_hours)->second(0);
        $ban->save();

        $ban->notes()->save($note);

        return $ban;
    }

    public function addNote($noteType, $noteContent, $writer = null, $attachment = null)
    {
        if (is_object($noteType)) {
            $noteType = $noteType->getKey();
        }

        if ($writer == null) {
            $writer = VATUK_ACCOUNT_SYSTEM;
        } elseif (is_object($writer)) {
            $writer = $writer->getKey();
        }

        $note               = new AccountNoteData();
        $note->account_id   = $this->id;
        $note->writer_id    = $writer;
        $note->note_type_id           = $noteType;
        $note->content      = $noteContent;
        $note->save();

        if (!is_null($attachment)) {
            $note->attachment()->save($attachment);
        }

        return $note;
    }

    public function setStatusFlag($flag)
    {
        $status = $this->attributes[ 'status' ];

        $status = $status | $flag; // OR

        $this->attributes[ 'status' ] = $status;
    }

    public function unSetStatusFlag($flag)
    {
        $status = $this->attributes[ 'status' ];

        $status = $status ^ $flag; // XOR

        $this->attributes[ 'status' ] = $status;
    }

    public function hasStatusFlag($flag)
    {
        return ($flag & $this->attributes[ "status" ]) == $flag; // AND
    }

    public function getIsInactiveAttribute()
    {
        return $this->hasStatusFlag(self::STATUS_INACTIVE);
    }

    public function setIsInactiveAttribute($value)
    {
        if ($value && !$this->is_inactive) {
            $this->setStatusFlag(self::STATUS_INACTIVE);
        } elseif (!$value && $this->is_inactive) {
            $this->unSetStatusFlag(self::STATUS_INACTIVE);
        }
    }

    public function getIsSystemAttribute()
    {
        return $this->hasStatusFlag(self::STATUS_SYSTEM);
    }

    public function setIsSystemAttribute($value)
    {
        if ($value && !$this->is_system) {
            $this->setStatusFlag(self::STATUS_SYSTEM);
        } elseif (!$value && $this->is_system) {
            $this->unSetStatusFlag(self::STATUS_SYSTEM);
        }
    }

    public function getIsSystemBannedAttribute()
    {
        $bans = $this->bans->filter(function($ban){
            return $ban->is_active && $ban->is_local;
        });

        return ($bans->count() > 0);
    }

    public function getSystemBanAttribute()
    {
        $bans = $this->bans->filter(function($ban){
            return $ban->is_active && $ban->is_local;
        });

        return $bans->first();
    }

    public function getIsNetworkBannedAttribute()
    {
        $bans = $this->bans->filter(function($ban){
            return $ban->is_active && $ban->is_network;
        });

        return ($bans->count() > 0);
    }

    public function getNetworkBanAttribute()
    {
        $bans = $this->bans->filter(function($ban){
            return $ban->is_active && $ban->is_network;
        });

        return $bans->first();
    }

    public function getIsBannedAttribute()
    {
        return ($this->is_system_banned || $this->is_network_banned);
    }

    public function getStatusStringAttribute()
    {
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

    public function getStatusArrayAttribute()
    {
        $stati = [];
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

    public function getLastLoginIpAttribute()
    {
        return long2ip($this->attributes[ 'last_login_ip' ]);
    }

    public function setLastLoginIpAttribute($value)
    {
        $this->attributes[ 'last_login_ip' ] = ip2long($value);
    }

    /**
     * Filter the attached secondary emails for those that are verified.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getVerifiedSecondaryEmailsAttribute()
    {
        if ($this->secondaryEmails->isEmpty()) {
            return collect();
        }

        return $this->secondaryEmails->filter(function ($email) {
            return $email->is_verified;
        });
    }

    /**
     * Set the name_first attribute with correct formatting.
     *
     * @param string $value The first name to format and store.
     */
    public function setNameFirstAttribute($value)
    {
        $this->attributes[ 'name_first' ] = format_name($value);
    }

    /**
     * Set the name_last attribute with correct formatting.
     *
     * @param string $value The last name to format and store.
     */
    public function setNameLastAttribute($value)
    {
        $this->attributes[ 'name_last' ] = format_name($value);
    }

    /**
     * Get the user's real full name.
     *
     * @return string
     */
    public function getRealNameAttribute()
    {
        return $this->name_first . ' ' . $this->name_last;
    }

    /**
     * Get the user's full name.
     *
     * If a nickname is set, that will be used in place of name_first.
     *
     * @return mixed|string
     */
    public function getNameAttribute()
    {
        if ($this->nickname != null) {
            return $this->nickname . ' ' . $this->name_last;
        }

        return $this->real_name;
    }

    /**
     * Alias of the getNameAttribute() method.
     *
     * @return mixed
     */
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    /**
     * Determine if the given name, matches either the user's nickname or real name.
     *
     * @param string $displayName The display name to verify.
     *
     * @return bool
     */
    public function isValidDisplayName($displayName)
    {
        return (strcasecmp($displayName, $this->name) == 0 || strcasecmp($displayName, $this->real_name) == 0);
    }

    public function getDisplayValueAttribute()
    {
        return $this->name . ' (' . $this->getKey() . ')';
    }

    public function toArray()
    {
        $array                   = parent::toArray();
        $array[ 'name' ]         = $this->name;
        $array[ 'name_real' ]    = $this->real_name;
        $array[ 'email' ]        = $this->email;
        $array[ 'atc_rating' ]   = $this->qualification_atc;
        $array[ 'atc_rating' ]   = ($array[ 'atc_rating' ] ? $array[ 'atc_rating' ]->name_long : '');
        $array[ 'pilot_rating' ] = [];
        foreach ($this->qualifications_pilot as $rp) {
            $array[ 'pilot_rating' ][] = $rp->code;
        }
        $array[ 'pilot_rating' ] = implode(', ', $array[ 'pilot_rating' ]);

        return $array;
    }

    public function determineState($region, $division)
    {
        // region=eur / division=gbr == division
        // region=eur / division!=gbr == region
        // region!=eur == international

        if ($region == 'EUR' AND $division == 'GBR') {
            $state = \App\Models\Mship\Account\State::STATE_DIVISION;
        } elseif ($region == 'EUR') {
            $state = \App\Models\Mship\Account\State::STATE_REGION;
        } else {
            $state = \App\Models\Mship\Account\State::STATE_INTERNATIONAL;
        }
        $this->states()->save(new Account\State(['state' => $state]));
    }

    public function getNewTsRegistrationAttribute()
    {
        return $this->teamspeakRegistrations->filter(function ($reg) {
            return is_null($reg->dbid);
        })->first();
    }

    /**
     * Returns what the session timeout for this user should be, in minutes.
     *
     * @return int The timeout in minutes
     */
    public function getSessionTimeoutAttribute()
    {
        $timeout = $this->roles->filter(function ($role) {
            return $role->hasSessionTimeout();
        })->pluck("session_timeout")->min();

        return ($timeout === null ? 0 : $timeout);
    }
}
