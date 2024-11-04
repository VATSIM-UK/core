<?php

namespace App\Models\Training;

use App\Events\Training\AccountAddedToWaitingList;
use App\Events\Training\FlagAddedToWaitingList;
use App\Events\Training\WaitingListCreated;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Models\Training\WaitingList\WaitingListFlag;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $department
 * @property bool $home_members_only
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $cts_theory_exam_level
 * @property array|null $feature_toggles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Account> $accounts
 * @property-read int|null $accounts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, WaitingListFlag> $flags
 * @property-read int|null $flags_count
 * @property-read object $feature_toggles_formatted
 * @property-read mixed $formatted_department
 * @property-read bool $should_check_atc_hours
 * @property-read bool $should_check_cts_theory_exam
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Account> $staff
 * @property-read int|null $staff_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, WaitingListAccount> $waitingListAccounts
 * @property-read int|null $waiting_list_accounts_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingList onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingList query()
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingList whereCtsTheoryExamLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingList whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingList whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingList whereFeatureToggles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingList whereHomeMembersOnly($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingList whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingList whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingList whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingList withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|WaitingList withoutTrashed()
 *
 * @mixin \Eloquent
 */
class WaitingList extends Model
{
    use SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        self::created(function ($model) {
            event(new WaitingListCreated($model));
        });
    }

    public $table = 'training_waiting_list';

    protected $fillable = ['name', 'slug', 'department', 'feature_toggles'];

    const ATC_DEPARTMENT = 'atc';

    const PILOT_DEPARTMENT = 'pilot';

    const ALL_FLAGS = 'all';

    const ANY_FLAGS = 'any';

    protected $casts = [
        'home_members_only' => 'boolean',
        'feature_toggles' => 'array',
        'deleted_at' => 'datetime',
    ];

    /**
     * A Waiting List can be managed by many Staff Members (Accounts).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function staff()
    {
        return $this->belongsToMany(
            Account::class,
            'training_waiting_list_staff',
            'list_id',
            'account_id'
        )->withTimestamps();
    }

    /**
     * Many WaitingLists can have many Accounts (pivot).
     *
     * @fixme remove when no longer used in filament stuff, use `waitingListAccounts` instead.
     *
     * @deprecated using a pivot here creates a bunch of N+1 problems for filament
     */
    public function accounts(): BelongsToMany
    {
        // this aint gonna work because the waitinglistaccount is no longer a pivot!

        return $this->belongsToMany(
            Account::class,
            'training_waiting_list_account',
            'list_id'
        )->using(WaitingListAccount::class)
            ->withPivot([
                'id',
                'deleted_at',
                'notes',
                'created_at',
            ])->wherePivot('deleted_at', null)->orderByPivot('created_at');
    }

    /**
     * Instead of using the pivot table as a pivot, go through two sets of joins to get to the account
     * This is to avoid N+1 problems in filament tables
     */
    public function waitingListAccounts(): HasMany
    {
        return $this->hasMany(
            WaitingListAccount::class,
            'list_id',
            'id'
        )->where('deleted_at', null)->orderBy('created_at');
    }

    /**
     * One WaitingList can have many flags associated with it.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function flags()
    {
        return $this->hasMany(WaitingListFlag::class, 'list_id');
    }

    /**
     * Find the position of a WaitingListAccount on this waiting list.
     */
    public function positionOf(WaitingListAccount $waitingListAccount): ?int
    {
        $key = $this->waitingListAccounts->search(fn (WaitingListAccount $listAccount) => $waitingListAccount->id === $listAccount->id);

        return ($key !== false) ? $key + 1 : null;
    }

    /**
     * Associate a flag with a waiting list.
     *
     * @return mixed
     */
    public function addFlag(WaitingListFlag $flag)
    {
        $savedFlag = $this->flags()->save($flag);

        $this->waitingListAccounts()->each(function (WaitingListAccount $listAccount) use ($flag) {
            $listAccount->flags()->attach($flag);
        });

        event(new FlagAddedToWaitingList($this));

        return $savedFlag;
    }

    /**
     * Remove a flag from a waiting list.
     *
     * @return mixed
     */
    public function removeFlag(WaitingListFlag $flag)
    {
        return $this->flags()->delete($flag);
    }

    /**
     * Add an Account to a waiting list.
     */
    public function addToWaitingList(Account $account, Account $staffAccount, ?Carbon $createdAt = null): WaitingListAccount
    {
        $timestamp = $createdAt != null ? $createdAt : Carbon::now();

        $waitingListAccount = new WaitingListAccount;
        $waitingListAccount->account_id = $account->id;
        $waitingListAccount->added_by = $staffAccount->id;

        $waitingListAccount = $this->waitingListAccounts()->save($waitingListAccount);

        // the following code is required as the timestamp for created_at gets overridden during the creation
        // process, despite being disabled on the pivot!!
        $waitingListAccount->created_at = $timestamp;
        $waitingListAccount->save();

        event(new AccountAddedToWaitingList($account, $this->fresh(), $staffAccount, $waitingListAccount));

        return $waitingListAccount;
    }

    public function includesAccount(int|Account $accountId): bool
    {
        if ($accountId instanceof Account) {
            $accountId = $accountId->id;
        }

        return $this->waitingListAccounts()->where('account_id', $accountId)->exists();
    }

    public function findWaitingListAccount(int|Account $accountId): ?WaitingListAccount
    {
        if ($accountId instanceof Account) {
            $accountId = $accountId->id;
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->waitingListAccounts()->where('account_id', $accountId)->first();
    }

    /**
     * Remove an Account from a waiting list.
     */
    public function removeFromWaitingList(Account $account): void
    {
        $waitingListAccount = $this->waitingListAccounts()->where('account_id', $account->id)->first();

        if (! $waitingListAccount) {
            return;
        }

        $waitingListAccount->delete();
    }

    public function isAtcList()
    {
        return $this->department == self::ATC_DEPARTMENT;
    }

    public function isPilotList()
    {
        return $this->department == self::PILOT_DEPARTMENT;
    }

    public function getFormattedDepartmentAttribute()
    {
        return match ($this->department) {
            self::ATC_DEPARTMENT => 'ATC Training',
            self::PILOT_DEPARTMENT => 'Pilot Training',
            default => ucfirst($this->department),
        };
    }

    public function getShouldCheckAtcHoursAttribute(): bool
    {
        return $this->feature_toggles['check_atc_hours'] ?? true;
    }

    public function getShouldCheckCtsTheoryExamAttribute(): bool
    {
        return $this->feature_toggles['check_cts_theory_exam'] ?? true;
    }

    public function getFeatureTogglesFormattedAttribute(): object
    {
        return (object) [
            'check_atc_hours' => $this->getShouldCheckAtcHoursAttribute(),
            'check_cts_theory_exam' => $this->getShouldCheckCtsTheoryExamAttribute(),
        ];
    }

    public function __toString()
    {
        return (string) $this->name;
    }
}
