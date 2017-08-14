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
            return self::findOrFail($accountId);
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
