<?php

namespace App\Models\Mship;

use App\Events\Mship\AccountAltered;
use App\Exceptions\Mship\InvalidCIDException;
use App\Jobs\UpdateMember;
use App\Libraries\Discord;
use App\Models\Model;
use App\Models\Mship\Account\Note as AccountNoteData;
use App\Models\Mship\Concerns\HasBans;
use App\Models\Mship\Concerns\HasCTSAccount;
use App\Models\Mship\Concerns\HasDiscordAccount;
use App\Models\Mship\Concerns\HasEmails;
use App\Models\Mship\Concerns\HasEndorsement;
use App\Models\Mship\Concerns\HasForumAccount;
use App\Models\Mship\Concerns\HasHelpdeskAccount;
use App\Models\Mship\Concerns\HasMoodleAccount;
use App\Models\Mship\Concerns\HasNetworkData;
use App\Models\Mship\Concerns\HasNotifications;
use App\Models\Mship\Concerns\HasPassword;
use App\Models\Mship\Concerns\HasQualifications;
use App\Models\Mship\Concerns\HasRoles;
use App\Models\Mship\Concerns\HasStates;
use App\Models\Mship\Concerns\HasTeamSpeakRegistrations;
use App\Models\Mship\Concerns\HasVisitTransferApplications;
use App\Models\Mship\Concerns\HasWaitingLists;
use App\Models\Mship\Note\Type;
use App\Models\Roster;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Models\Role;
use Watson\Rememberable\Rememberable;

/**
 * App\Models\Mship\Account.
 *
 * @property int $id
 * @property string $name_first
 * @property string $name_last
 * @property string|null $nickname
 * @property string|null $email
 * @property string|null $password
 * @property \Illuminate\Support\Carbon|null $password_set_at
 * @property \Illuminate\Support\Carbon|null $password_expires_at
 * @property \Illuminate\Support\Carbon|null $last_login
 * @property string $last_login_ip
 * @property string|null $remember_token
 * @property int|null $discord_id
 * @property string|null $discord_access_token
 * @property string|null $discord_refresh_token
 * @property string|null $vatsim_access_token
 * @property string|null $vatsim_refresh_token
 * @property int|null $vatsim_token_expires
 * @property string|null $gender
 * @property string|null $experience
 * @property int $age
 * @property bool $inactive
 * @property int $is_invisible
 * @property int $debug
 * @property \Illuminate\Support\Carbon|null $joined_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $cert_checked_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sys\Activity> $activityRecent
 * @property-read int|null $activity_recent_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mship\Account\Ban> $bans
 * @property-read int|null $bans_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mship\Account\Ban> $bansAsInstigator
 * @property-read int|null $bans_as_instigator_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Client> $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sys\Data\Change> $dataChanges
 * @property-read int|null $data_changes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mship\Account\Endorsement> $endorsements
 * @property-read int|null $endorsements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mship\Feedback\Feedback> $feedback
 * @property-read int|null $feedback_count
 * @property-read mixed $active_qualifications
 * @property-read mixed $discord_name
 * @property-read \App\Models\Mship\RestCord/Model/User/User $discord_user
 * @property-read mixed $full_name
 * @property-read mixed $fully_defined
 * @property-read mixed $has_unread_important_notifications
 * @property-read mixed $has_unread_must_acknowledge_notifications
 * @property-read mixed $has_unread_notifications
 * @property-read mixed $is_banned
 * @property mixed $is_inactive
 * @property-read mixed $is_network_banned
 * @property-read bool $is_on_network
 * @property-read mixed $is_system_banned
 * @property-read bool $mandatory_password
 * @property-read mixed|string $name_preferred
 * @property-read mixed $network_ban
 * @property-read mixed $new_ts_registration
 * @property-read int $password_lifetime
 * @property-read \Illuminate\Support\Collection $permanent_states
 * @property-read mixed $primary_permanent_state
 * @property-read mixed $primary_state
 * @property-read mixed $qualification_atc
 * @property-read mixed $qualification_pilot
 * @property-read mixed $qualification_pilot_military
 * @property-read mixed $qualifications_admin
 * @property-read mixed $qualifications_atc
 * @property-read mixed $qualifications_atc_training
 * @property-read mixed $qualifications_pilot
 * @property-read mixed $qualifications_pilot_military
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
 * @property-read mixed $has_controller_rating
 * @property-read mixed $name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NetworkData\Atc> $networkDataAtc
 * @property-read int|null $network_data_atc_count
 * @property-read \App\Models\NetworkData\Atc|null $networkDataAtcCurrent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NetworkData\Pilot> $networkDataPilot
 * @property-read int|null $network_data_pilot_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AccountNoteData> $noteWriter
 * @property-read int|null $note_writer_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AccountNoteData> $notes
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Client> $oAuthClients
 * @property-read int|null $o_auth_clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Token> $oAuthTokens
 * @property-read int|null $o_auth_tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Smartcars\Pirep> $pireps
 * @property-read int|null $pireps_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mship\Qualification> $qualifications
 * @property-read int|null $qualifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sys\Notification> $readSystemNotifications
 * @property-read int|null $read_system_notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read Roster|null $roster
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mship\Account\Email> $secondaryEmails
 * @property-read int|null $secondary_emails_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sso\Email> $ssoEmails
 * @property-read int|null $sso_emails_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mship\State> $states
 * @property-read int|null $states_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mship\State> $statesHistory
 * @property-read int|null $states_history_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TeamSpeak\Registration> $teamspeakRegistrations
 * @property-read int|null $teamspeak_registrations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sys\Token> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VisitTransfer\Application> $visitTransferApplications
 * @property-read int|null $visit_transfer_applications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VisitTransfer\Reference> $visitTransferReferee
 * @property-read int|null $visit_transfer_referee_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Training\WaitingList> $waitingLists
 * @property-read int|null $waiting_lists_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Account banned()
 * @method static \Database\Factories\Mship\AccountFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Account newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account notBanned()
 * @method static \Illuminate\Database\Eloquent\Builder|Account onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Account permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|Account query()
 * @method static \Illuminate\Database\Eloquent\Builder|Account role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereCertCheckedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereDebug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereDiscordAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereDiscordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereDiscordRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereExperience($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereInactive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereIsInvisible($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereJoinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereNameFirst($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereNameLast($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account wherePasswordExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account wherePasswordSetAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereVatsimAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereVatsimRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereVatsimTokenExpires($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Account withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Account extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable,
        Authorizable,
        HasBans,
        HasCTSAccount,
        HasDiscordAccount,
        HasEmails,
        HasEndorsement,
        HasFactory,
        HasForumAccount,
        HasHelpdeskAccount,
        HasMoodleAccount,
        HasNetworkData,
        HasNotifications,
        HasPassword,
        HasQualifications,
        HasRoles,
        HasStates,
        HasTeamSpeakRegistrations,
        HasVisitTransferApplications,
        HasWaitingLists,
        Notifiable,
        Rememberable,
        SoftDeletingTrait;
    use HasApiTokens {
        clients as oAuthClients;
        tokens as oAuthTokens;
        token as oAuthToken;
        tokenCan as oAuthTokenCan;
        createToken as createOAuthToken;
        withAccessToken as withOAuthAccessToken;
    }

    protected $table = 'mship_account';

    protected $guard_name = ['web'];

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name_first',
        'name_last',
        'nickname',
        'email',
        'password',
        'password_set_at',
        'password_expires_at',
        'joined_at',
        'cert_checked_at',
    ];

    protected $attributes = [
        'inactive' => false,
        'last_login_ip' => '0.0.0.0',
    ];

    protected $untracked = ['cert_checked_at', 'last_login', 'remember_token', 'password', 'updated_at'];

    protected $trackedEvents = ['created', 'updated', 'deleted', 'restored'];

    protected $casts = [
        'inactive' => 'boolean',
        'discord_id' => 'int',
        'last_login' => 'datetime',
        'joined_at' => 'datetime',
        'cert_checked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'password_set_at' => 'datetime',
        'password_expires_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'discord_access_token',
        'discord_refresh_token',
        'vatsim_access_token',
        'vatsim_refresh_token',
    ];

    protected static function boot()
    {
        parent::boot();

        self::saved(function ($user) {
            if (count(array_except($user->getDirty(), $user->untracked)) > 0) {
                event(new AccountAltered($user));
            }
        });

        self::created([get_called_class(), 'eventCreated']);
    }

    /**
     * @param  Account  $model
     */
    public static function eventCreated($model, $extra = null, $data = null)
    {
        // Add to default role
        $defaultRole = Role::where('default', 1)->limit(1)->first();
        $model->assignRole($defaultRole);
    }

    /**
     * Find an account by its ID or retrieve it from Cert. If false, user does not exist at VATSIM.NET.
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null|static|bool|static[]
     *
     * @throws InvalidCIDException
     */
    public static function findOrRetrieve($accountId)
    {
        if (! is_numeric($accountId)) {
            throw new InvalidCIDException;
        }

        try {
            return self::findOrFail((int) $accountId);
        } catch (ModelNotFoundException $e) {
            dispatch((new UpdateMember($accountId))->onConnection('sync'));

            $account = self::find($accountId);

            if (! $account) {
                // User doesn't exist at VATSIM.NET
                throw new InvalidCIDException;
            }

            return $account;
        }
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

    public function pireps()
    {
        return $this->hasManyThrough(\App\Models\Smartcars\Pirep::class, \App\Models\Smartcars\Bid::class, 'account_id', 'bid_id', 'id');
    }

    public function feedback()
    {
        return $this->hasMany(\App\Models\Mship\Feedback\Feedback::class);
    }

    public function addNote($noteType, $noteContent, $writer = null, $attachment = null)
    {
        if (is_string($noteType)) {
            $noteType = Type::isShortCode($noteType)->first();
        }
        if (is_object($noteType) && $noteType->exists) {
            $noteType = $noteType->getKey();
        } else {
            $noteType = Type::isDefault()->first()->getKey();
        }

        if (! is_null($writer) && is_object($writer)) {
            $writer = $writer->getKey();
        }

        $note = new AccountNoteData;
        $note->account_id = $this->id;
        $note->writer_id = $writer;
        $note->note_type_id = $noteType;
        $note->content = $noteContent;
        $note->save();

        if (! is_null($attachment)) {
            $note->attachment()->associate($attachment)->save();
        }

        return $note;
    }

    public function roster(): HasOne
    {
        return $this->hasOne(Roster::class, 'account_id', 'id');
    }

    public function onRoster(): bool
    {
        return $this->roster?->account_id == $this->id;
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
     * @param  string  $value  The first name to format and store.
     */
    public function setNameFirstAttribute($value)
    {
        $this->attributes['name_first'] = format_name($value);
    }

    /**
     * Set the name_last attribute with correct formatting.
     *
     * @param  string  $value  The last name to format and store.
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
     * @return mixed|string
     */
    public function name(): Attribute
    {
        return Attribute::make(get: fn () => $this->name_preferred.' '.$this->name_last);
    }

    /**
     * Get the user's first name.
     *
     * If a nickname is set, that will be used in place of name_first.
     *
     * @return mixed|string
     */
    public function getNamePreferredAttribute()
    {
        return $this->nickname ? $this->nickname : $this->name_first;
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

    public function getFullyDefinedAttribute()
    {
        return $this->name_first && $this->name_last && $this->email && $this->qualification_atc;
    }

    private function allowedNames($includeATC = false, $withNumberWildcard = false)
    {
        $allowedNames = collect();

        return $allowedNames->push($this->name)
            ->push($this->real_name)
            ->each(function ($item) use (&$allowedNames) {
                $allowedNames->push("{$item} {$this->id}");
            })->when($includeATC && $this->networkDataAtcCurrent, function (&$allowedNames) {
                $allowedNames->each(function ($item) use (&$allowedNames) {
                    $allowedNames->push($item." - {$this->networkDataAtcCurrent->callsign}");
                });
            })->when($withNumberWildcard, function (&$allowedNames) {
                $allowedNames->each(function ($item) use (&$allowedNames) {
                    $allowedNames->push($item."\d");
                });
            });
    }

    /**
     * Determine if the given name, matches either the user's nickname or real name.
     *
     * @param  string  $displayName  The display name to verify.
     * @return bool
     */
    public function isValidDisplayName($displayName)
    {
        return ! $this->allowedNames(true)->filter(function ($item, $key) use ($displayName) {
            return strcmp($item, $displayName) == 0;
        })->isEmpty();
    }

    public function isPartiallyValidDisplayName($displayName)
    {
        return ! $this->allowedNames()->filter(function ($item, $key) use ($displayName) {
            return strstr(strtolower($displayName), strtolower($item)) != false;
        })->isEmpty();
    }

    public function isDuplicateDisplayName($displayName)
    {
        return ! $this->allowedNames(true, true)->filter(function ($item, $key) use ($displayName) {
            return preg_match('/^'.$item.'$/i', $displayName) == 1;
        })->isEmpty();
    }

    /**
     * Returns the Discord user associated with this account if the user has linked it.
     *
     * @return RestCord/Model/User/User The Discord user
     */
    public function getDiscordUserAttribute()
    {
        return app()->make(Discord::class)->getUserInformation($this);
    }

    /**
     * Returns what the session timeout for this user should be, in minutes.
     *
     * @return int The timeout in minutes
     */
    public function getSessionTimeoutAttribute()
    {
        $timeout = $this->roles()
            ->orderBy('session_timeout', 'DESC')
            ->first()
            ->session_timeout;

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
        // sort pilot ratings in order Pilot -> Military
        foreach ($this->qualifications_pilot as $rp) {
            $array['pilot_rating'][] = $rp->code;
        }
        foreach ($this->qualifications_pilot_military as $rp) {
            $array['pilot_rating'][] = $rp->code;
        }

        $array['pilot_rating'] = implode(', ', $array['pilot_rating']);

        return $array;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function hiddenFeedbackUsers()
    {
        if ($this->can('use-permission', 'feedback/own') || $this->can('use-permission', 'feedback.view-own')) {
            return [];
        }

        return [$this->id];
    }
}
