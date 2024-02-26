<?php

namespace App\Models;

use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Atc\PositionGroupPosition;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Roster
 *
 * @property-read Account|null $account
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Roster newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Roster newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Roster query()
 *
 * @mixin \Eloquent
 */
class Roster extends Model
{
    use HasFactory;

    protected $table = 'roster';

    protected $fillable = ['account_id'];

    protected static function booted(): void
    {
        // Only return users that are on the roster
        // and have not changed state since
        // joining the roster.
        static::addGlobalScope('eligibleState', function (Builder $builder) {
            $builder->whereHas('account', function ($query) {
                $query->whereHas('states', function ($query) {
                    $query
                        ->join('roster', 'mship_account_state.account_id', '=', 'roster.account_id')
                        ->whereIn('mship_state.code', ['DIVISION', 'VISITING', 'TRANSFERRING'])
                        ->whereColumn('roster.created_at', '>', 'mship_account_state.start_at');
                });
            });
        });
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function remove()
    {
        // Notify that they were removed (database and email)
        // Remove from waiting lists too
        $this->delete();
    }

    public function accountCanControl(Position $position)
    {
        // If the account is not on the roster,
        // they cannot control.
        if (! $this->account) {
            return false;
        }

        // If the position is part of a group,
        // a) do they have the endorsement for that group or
        // b) do they have a rating higher than the maximum rating for the group
        if ($positionGroupPosition = PositionGroupPosition::where('position_id', $position->id)->first()) {
            return $this->account->qualification_atc->vatsim
                > $positionGroupPosition->positionGroup?->maximumAtcQualification?->vatsim
            || $this->account
                ->endorsements()
                ->active()
                ->whereHasMorph('endorsable',
                    PositionGroup::class,
                    fn ($query) => $query->where('id', $positionGroupPosition->position_group_id)
                )
                ->exists();
        }

        // If the position is above their rating, do they
        // have an active solo endorsement?
        if ($position->getMinimumVatsimQualificationAttribute() > $this->account->qualification_atc->vatsim) {
            return $this->account
                ->endorsements()
                ->active()
                ->whereHasMorph('endorsable',
                    Position::class,
                    fn ($query) => $query->where('id', $position->id)
                )
                ->exists();
        }

        // If they are visiting or transferring, they need to have been
        // specifically given permission to control up to their rating
        if ($this->account->hasState('VISITING') || $this->account->hasState('TRANSFERRING')) {
            return $this->account
                ->endorsements()
                ->active()
                ->whereHasMorph('endorsable',
                    Qualification::class
                )
                ->get()
                ->sortByDesc('endorsable.vatsim')
                ->first()?->endorsable?->vatsim >= $position->getMinimumVatsimQualificationAttribute();
        }

        // They can control unrestricted up to their rating and
        // the position isn't restricted by an endorsement
        return true;
    }
}
