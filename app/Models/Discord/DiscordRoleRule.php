<?php

namespace App\Models\Discord;

use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\Permission;
use App\Models\Roster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DiscordRoleRule extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'permission_id' => 'int',
        'discord_id' => 'string',
        'position_type' => 'int',
    ];

    /**
     * Permission Association.
     *
     * When associated with a permission, this association should only be applied if the user has that permission
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * Qualification Association.
     *
     * When associated with a qualification, this association should only be applied if the user has that qualification active
     */
    public function qualification()
    {
        return $this->belongsTo(Qualification::class);
    }

    /**
     * Membership State Association.
     *
     * When associated with a state, this association should only be applied if the user is in that state
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function endorsable()
    {
        return $this->morphTo();
    }

    /**
     * Determine if account satifies the requirements for the role.
     */
    public function accountSatisfies(Account $account): bool
    {
        return $this->accountSatisfiesPermissionRequirement($account)
            && $this->accountSatisfiesQualificationRequirement($account)
            && $this->accountSatisfiesStateRequirement($account)
            && $this->accountSatisfiesCTSMayControlRequirement($account)
            && $this->accountSatisfiesCanControlOnRosterRequirement($account)
            && $this->accountSatisfiesSoloEndorsementPositionTypeRequirement($account);
    }

    protected function accountSatisfiesPermissionRequirement(Account $account): bool
    {
        return ! $this->permission_id || $account->hasPermissionTo($this->permission_id);
    }

    protected function accountSatisfiesQualificationRequirement(Account $account): bool
    {
        return ! $this->qualification_id || $account->hasQualification($this->qualification);
    }

    protected function accountSatisfiesStateRequirement(Account $account): bool
    {
        return ! $this->state_id || $account->hasState($this->state);
    }

    protected function accountSatisfiesCTSMayControlRequirement(Account $account): bool
    {
        if (! $this->cts_may_control_contains) {
            return true;
        }

        $ctsMember = Member::where('cid', $account->getKey())->first();
        if (! $ctsMember || ! $ctsMember->visit_may_control) {
            return false;
        }

        return Str::contains($ctsMember->visit_may_control, $this->cts_may_control_contains);
    }

    protected function accountSatisfiesCanControlOnRosterRequirement(Account $account): bool
    {
        if ($this->endorsable instanceof Position) {
            return Roster::firstWhere('account_id', $account->getKey())
                ?->accountCanControl($this->endorsable);
        }

        if ($this->endorsable instanceof PositionGroup) {
            return Roster::where('account_id', $account->getKey())->exists()
                && $account
                    ->endorsements()
                    ->active()
                    ->whereHasMorph('endorsable',
                        PositionGroup::class,
                        fn ($query) => $query->where('id', $this->endorsable->getKey())
                    )
                    ->exists();
        }

        return true;
    }

    protected function accountSatisfiesSoloEndorsementPositionTypeRequirement(Account $account): bool
    {
        if (! $this->position_type) {
            return true;
        }

        return $account
            ->endorsements()
            ->active()
            ->whereHasMorph('endorsable', [Position::class], function (Builder $query) {
                $query->where('type', $this->position_type);
            })
            ->exists();
    }
}
