<?php

namespace App\Models\Mship;

use App\Exceptions\Mship\InvalidCIDException;
use App\Jobs\UpdateMember;
use App\Models\Mship\Account\Note as AccountNoteData;
use App\Models\Mship\Concerns\HasBans;
use App\Models\Mship\Concerns\HasCommunityGroups;
use App\Models\Mship\Concerns\HasEmails;
use App\Models\Mship\Concerns\HasHelpdeskAccount;
use App\Models\Mship\Concerns\HasMoodleAccount;
use App\Models\Mship\Concerns\HasNetworkData;
use App\Models\Mship\Concerns\HasNotifications;
use App\Models\Mship\Concerns\HasPassword;
use App\Models\Mship\Concerns\HasQualifications;
use App\Models\Mship\Concerns\HasStates;
use App\Models\Mship\Concerns\HasTeamSpeakRegistrations;
use App\Models\Mship\Concerns\HasVisitTransferApplications;
use App\Models\Mship\Note\Type;
use App\Models\Mship\Permission as PermissionData;
use App\Models\Mship\Role as RoleData;
use App\Events\Mship\Bans\AccountBanned;
use Illuminate\Notifications\Notifiable;
use App\Models\VisitTransfer\Application;
use App\Jobs\Mship\Account\MemberCertUpdate;
use App\Notifications\Mship\SlackInvitation;
use App\Traits\RecordsActivity as RecordsActivityTrait;
use App\Traits\RecordsDataChanges as RecordsDataChangesTrait;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Watson\Rememberable\Rememberable;

/**
 * App\Models\Mship\Account
 *
 * @property int $id
 * @property string|null $slack_id
 * @property string $name_first
 * @property string $name_last
 * @property string|null $nickname
 * @property string|null $email
 * @property string|null $password
 * @property \Carbon\Carbon|null $password_set_at
 * @property \Carbon\Carbon|null $password_expires_at
 * @property \Carbon\Carbon|null $last_login
 * @property string $last_login_ip
 * @property string|null $remember_token
 * @property string|null $gender
 * @property string|null $experience
 * @property int $age
 * @property bool $inactive
 * @property int $is_invisible
 * @property int $debug
 * @property \Carbon\Carbon|null $joined_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $cert_checked_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Activity[] $activityRecent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Ban[] $bans
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Ban[] $bansAsInstigator
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[] $clients
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Community\Group[] $communityGroups
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Feedback\Feedback[] $feedback
 * @property-read mixed $active_qualifications
 * @property-read mixed $full_name
 * @property-read mixed $has_unread_important_notifications
 * @property-read mixed $has_unread_must_acknowledge_notifications
 * @property-read mixed $has_unread_notifications
 * @property-read mixed $is_banned
 * @property mixed $is_inactive
 * @property-read mixed $is_network_banned
 * @property-read bool $is_on_network
 * @property-read mixed $is_system_banned
 * @property-read bool $mandatory_password
 * @property-read mixed|string $name
 * @property-read mixed $network_ban
 * @property-read mixed $new_ts_registration
 * @property-read int $password_lifetime
 * @property-read \Illuminate\Support\Collection $permanent_states
 * @property-read mixed $primary_permanent_state
 * @property-read mixed $primary_state
 * @property-read mixed $qualification_atc
 * @property-read mixed $qualifications_admin
 * @property-read mixed $qualifications_atc
 * @property-read mixed $qualifications_atc_training
 * @property-read mixed $qualifications_pilot
 * @property-read mixed $qualifications_pilot_string
 * @property-read mixed $qualifications_pilot_training
 * @property-read string $real_name
 * @property-read int $session_timeout
 * @property-read mixed $status_string
 * @property-read mixed $system_ban
 * @property-read \Illuminate\Support\Collection $temporary_states
 * @property-read mixed $unread_must_acknowledge_notifications
 * @property-read int $unread_must_acknowledge_time_elapsed
 * @property-read mixed $unread_notifications
 * @property-read \Illuminate\Support\Collection $verified_secondary_emails
 * @property-read mixed $visit_transfer_current
 * @property-read mixed $visit_transfer_referee_pending
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Messages\Thread\Post[] $messagePosts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Messages\Thread[] $messageThreads
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NetworkData\Atc[] $networkDataAtc
 * @property-read \App\Models\NetworkData\Atc $networkDataAtcCurrent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Note[] $noteWriter
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Note[] $notes
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[] $oAuthClients
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Token[] $oAuthTokens
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Qualification[] $qualifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Notification[] $readSystemNotifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Role[] $roles
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Email[] $secondaryEmails
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sso\Email[] $ssoEmails
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\State[] $states
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\State[] $statesHistory
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TeamSpeak\Registration[] $teamspeakRegistrations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Token[] $tokens
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\VisitTransfer\Application[] $visitTransferApplications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\VisitTransfer\Reference[] $visitTransferReferee
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereCertCheckedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereDebug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereExperience($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereInactive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereIsInvisible($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereJoinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereNameFirst($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereNameLast($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account wherePasswordExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account wherePasswordSetAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereSlackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Mship\Account whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Mship\Account withoutTrashed()
 * @mixin \Eloquent
 */
class Account extends \App\Models\Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use SoftDeletingTrait, Rememberable, Notifiable, Authenticatable, Authorizable, RecordsActivityTrait,
        RecordsDataChangesTrait, HasCommunityGroups, HasNetworkData, HasMoodleAccount, HasHelpdeskAccount,
        HasVisitTransferApplications, HasQualifications, HasStates, HasBans, HasTeamSpeakRegistrations, HasPassword, HasNotifications, HasEmails;
    use HasApiTokens {
        clients as oAuthClients;
        tokens as oAuthTokens;
        token as oAuthToken;
        tokenCan as oAuthTokenCan;
        createToken as createOAuthToken;
        withAccessToken as withOAuthAccessToken;
    }

    protected $table = 'mship_account';
    public $incrementing = false;
    protected $dates = [
        'last_login',
        'joined_at',
        'cert_checked_at',
        'created_at',
        'updated_at',
        'deleted_at',
        'password_set_at',
        'password_expires_at',
    ];
    protected $fillable = [
        'id',
        'name_first',
        'name_last',
        'email',
        'password',
        'password_set_at',
        'password_expires_at',
    ];
    protected $attributes = [
        'name_first' => '',
        'name_last' => '',
        'inactive' => false,
        'last_login_ip' => '0.0.0.0',
    ];
    protected $doNotTrack = ['cert_checked_at', 'last_login', 'remember_token', 'password'];
    protected $casts = ['inactive' => 'boolean'];

    public function routeNotificationForSlack()
    {
        return env('SLACK_ENDPOINT');
    }

    /**
     * @param Account $model
     * @param null $extra
     * @param null $data
     */
    public static function eventCreated($model, $extra = null, $data = null)
    {
        parent::eventCreated($model, $extra, $data);

        // Add the user to the default role.
        $defaultRole = RoleData::isDefault()->first();

        if ($defaultRole) {
            $model->roles()->attach($defaultRole);
        }

        // Queue the slack email
        $model->notify((new SlackInvitation())->delay(Carbon::now()->addDays(7)));
    }

    /**
     * Find an account by its ID or retrieve it from Cert.
     *
     * @param $accountId
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null|static|static[]
     * @throws InvalidCIDException
     */
    public static function findOrRetrieve($accountId)
    {
        if (!is_numeric($accountId)) {
            throw new InvalidCIDException();
        }

        try {
            return self::findOrFail((int) $accountId);
        } catch (ModelNotFoundException $e) {
            dispatch((new UpdateMember($accountId))->onConnection('sync'));

            $account = self::find($accountId);

            return $account;
        }
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
        return $this->hasMany(\App\Models\Sys\Activity::class, 'actor_id');
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

    public function feedback()
    {
        return $this->hasMany(\App\Models\Mship\Feedback\Feedback::class);
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
            $this->attributes['password'] = null;

            return;
        }

        $this->attributes['password'] = \Hash::make($password);
    }

    /**
     * Determine whether the current account has a password set.
     *
     * @return bool
     */
    public function hasPassword()
    {
        return $this->password !== null;
    }

    /**
     * Determine whether the current password has expired.
     *
     * @return bool
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
     * @return int
     */
    public function getPasswordLifetimeAttribute()
    {
        return $this->roles->filter(function ($role) {
            return $role->hasPasswordLifetime();
        })->pluck('password_lifetime')
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
     * @param bool $temporary Should we treat the password as temporary?
     *
     * @return null|Carbon
     */
    public function calculatePasswordExpiry($temporary = false)
    {
        if ($temporary) {
            return Carbon::now();
        }

        if ($this->password_lifetime > 0) {
            return Carbon::now()->addDays($this->password_lifetime);
        }
    }

    /**
     * Set the user's password.
     *
     * @param string $password The password string.
     * @param bool $temporary Will only be a temporary password
     * @return bool
     */
    public function setPassword($password, $temporary = false)
    {
        $save = $this->fill([
            'password' => $password,
            'password_set_at' => Carbon::now(),
            'password_expires_at' => $this->calculatePasswordExpiry($temporary),
        ])->save();

        // if the password is being reset by its owner...
        if ($save && \Auth::check() && \Auth::user()->id === $this->id) {
            \Session::put([
                'password_hash' => \Auth::user()->getAuthPassword(),
            ]);
            \Session::put('auth.secondary', Carbon::now());
        }

        return $save;
    }

    /**
     * Remove a member's current password.
     *
     * @return bool
     */
    public function removePassword()
    {
        return $this->fill([
            'password' => null,
            'password_set_at' => null,
            'password_expires_at' => null,
        ])->save();
    }

    /**
     * Determine if the current account has the given email attached to it.
     *
     * @param string $email        The email to check is attached to this account.
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
     *
     * @return bool
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

        $this->attributes['email'] = strtolower($primaryEmail);

        return $this->save();
    }

    /**
     * Laravel magic setter - calls the setEmail method and instantly saves.
     *
     * @param string $email
     *
     * @return bool
     */
    public function setEmailAttribute($email)
    {
        return $this->setEmail($email);
    }

    /**
     * Attach a new secondary email to this user account.
     *
     * @param string $newEmail The new email address to add to this account.
     * @param bool   $verified Set to TRUE if the email should be automatically verified.
     *
     * @return \Illuminate\Database\Eloquent\Model|Email|false
     */
    public function addSecondaryEmail($newEmail, $verified = false)
    {
        if (!$this->hasEmail($newEmail)) {
            $newSecondaryEmail = new AccountEmail(['email' => $newEmail]);
            $newSecondaryEmail->verified_at = ($verified ? Carbon::now() : null);

            return $this->secondaryEmails()->save($newSecondaryEmail);
        }

        return $this->secondaryEmails->filter(function ($e) use ($newEmail) {
            return strcasecmp($e->email, $newEmail) == 0;
        })->first();
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
     */
    public function addQualification(Qualification $qualification)
    {
        if (!$this->hasQualification($qualification)) {
            $this->qualifications()->attach($qualification);
            $this->touch();
        }

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
            $writerId = 0;
        } elseif (is_object($writerId)) {
            $writerId = $writerId->getKey();
        }

        // Attach the note.
        $note = $this->addNote(Type::isShortCode('discipline')->first(), $banNote, $writerId);

        // Make a ban.
        $ban = new Ban();
        $ban->account_id = $this->id;
        $ban->banned_by = $writerId;
        $ban->type = $type;
        $ban->reason_id = $banReason->id;
        $ban->reason_extra = $banExtraReason;
        $ban->period_start = Carbon::now()->second(0);
        $ban->period_finish = Carbon::now()->addHours($banReason->period_hours)->second(0);
        $ban->save();

        $ban->notes()->save($note);

        event(new AccountBanned($ban));

        return $ban;
    }

    public function addNote($noteType, $noteContent, $writer = null, $attachment = null)
    {
        if (is_string($noteType)) {
            $noteType = Type::isShortCode('visittransfer')->first();
        }
        if (is_object($noteType) && $noteType->exists) {
            $noteType = $noteType->getKey();
        } else {
            $noteType = Type::isDefault()->first()->getKey();
        }

        if ($writer == null) {
            $writer = 0;
        } elseif (is_object($writer)) {
            $writer = $writer->getKey();
        }

        $note = new AccountNoteData();
        $note->account_id = $this->id;
        $note->writer_id = $writer;
        $note->note_type_id = $noteType;
        $note->content = $noteContent;
        $note->save();

        if (!is_null($attachment)) {
            $note->attachment()->save($attachment);
        }

        return $note;
    }

    public function getIsInactiveAttribute()
    {
        return $this->inactive;
    }

    public function setIsInactiveAttribute($value)
    {
        $this->inactive = (bool) $value;
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
        } else {
            return trans('mship.account.status.active');
        }
    }

    /**
     * Set the name_first attribute with correct formatting.
     *
     * @param string $value The first name to format and store.
     */
    public function setNameFirstAttribute($value)
    {
        $this->attributes['name_first'] = format_name($value);
    }

    /**
     * Set the name_last attribute with correct formatting.
     *
     * @param string $value The last name to format and store.
     */
    public function setNameLastAttribute($value)
    {
        $this->attributes['name_last'] = format_name($value);
    }

    /**
     * Get the user's real full name.
     *
     * @return string
     */
    public function getRealNameAttribute()
    {
        return $this->name_first.' '.$this->name_last;
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
            return $this->nickname.' '.$this->name_last;
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
        $allowedNames = collect();
        $allowedNames->push($this->name);
        $allowedNames->push($this->real_name);

        if ($this->networkDataAtcCurrent) {
            $allowedNames->push($this->name.' - '.$this->networkDataAtcCurrent->callsign);
            $allowedNames->push($this->real_name.' - '.$this->networkDataAtcCurrent->callsign);
        }

        return $allowedNames->filter(function ($item, $key) use ($displayName) {
            return strcasecmp($item, $displayName) == 0;
        })->count() > 0;
    }

    public function isPartiallyValidDisplayName($displayName)
    {
        $allowedNames = collect();
        $allowedNames->push($this->name);
        $allowedNames->push($this->real_name);

        return $allowedNames->filter(function ($item, $key) use ($displayName) {
            return strstr(strtolower($displayName), strtolower($item)) != false;
        })->count() > 0;
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
        })->pluck('session_timeout')->min();

        return $timeout === null ? 0 : $timeout;
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['name'] = $this->name;
        $array['name_real'] = $this->real_name;
        $array['email'] = $this->email;
        $array['atc_rating'] = $this->qualification_atc;
        $array['atc_rating'] = ($array['atc_rating'] ? $array['atc_rating']->name_long : '');
        $array['pilot_rating'] = [];
        foreach ($this->qualifications_pilot as $rp) {
            $array['pilot_rating'][] = $rp->code;
        }
        $array['pilot_rating'] = implode(', ', $array['pilot_rating']);

        return $array;
    }

    public function __toString()
    {
        return $this->name;
    }
}
