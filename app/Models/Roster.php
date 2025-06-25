<?php

namespace App\Models;

use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Atc\PositionGroupPosition;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use App\Models\Mship\Account\Note;
use App\Models\Mship\Qualification;
use App\Notifications\Roster\RemovedFromRoster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

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
                        ->orWhereColumn('roster.updated_at', '>', 'mship_account_state.start_at');
                });
            });
        });
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function restrictionNote()
    {
        return $this->belongsTo(Note::class, 'restriction_note_id');
    }

    public function remove(?RosterUpdate $update = null)
    {
        DB::transaction(function () use ($update) {
            RosterHistory::create([
                'account_id' => $this->account_id,
                'original_created_at' => $this->created_at,
                'original_updated_at' => $this->updated_at,
                'removed_by' => auth()->user()?->getKey(),
                'roster_update_id' => $update?->id,
            ]);

            $this->delete();

            $this->account->notify(new RemovedFromRoster);
        });
    }

    public function accountCanControl(Position $position)
    {
        // If the account is not on the roster,
        // they cannot control.
        if (! $this->account) {
            return false;
        }

        $assignedPositionGroupsWithPosition = Endorsement::where('account_id', $this->account->id)
            ->whereHasMorph('endorsable', PositionGroup::class, fn ($query) => $query->whereHas('positions', fn ($query) => $query->where('positions.id', $position->id)))
            ->get()
            ->map(fn ($endorsement) => $endorsement->endorsable);

        $unassignedPositionGroupsWithPosition = PositionGroup::whereHas('positions', fn ($query) => $query->where('positions.id', $position->id))
            ->whereDoesntHave('membershipEndorsement', fn ($query) => $query->where('account_id', $this->account->id))
            ->get();

        $checkPositionForPositionGroup = function (PositionGroupPosition $positionGroupPosition) {
            // If the position is part of a group,
            // a) are they a home member with a rating above the position's maximum?
            // b) are they a visiting or transferring member with an endorsement up to a rating above the position group's maximum?
            // c) are they endorsed on this specific position group?
            $isEntitledByHomeMemberRating = isset($positionGroupPosition->positionGroup?->maximumAtcQualification)
                && $this->account->hasState('DIVISION') &&
                $this->account->qualification_atc->vatsim > $positionGroupPosition->positionGroup?->maximumAtcQualification?->vatsim;

            $isEndorsedToRating = isset($positionGroupPosition->positionGroup?->maximumAtcQualification)
                && ($this->account->hasState('VISITING') || $this->account->hasState('TRANSFERRING'))
                && $this->account
                    ->endorsements()
                    ->active()
                    ->whereHasMorph('endorsable',
                        Qualification::class
                    )
                    ->get()
                    ->sortByDesc('endorsable.vatsim')
                    ->first()?->endorsable?->vatsim > $positionGroupPosition->positionGroup?->maximumAtcQualification?->vatsim;

            $hasEndorsementForPositionGroup = $this->account
                ->endorsements()
                ->active()
                ->whereHasMorph('endorsable',
                    PositionGroup::class,
                    fn ($query) => $query->where('id', $positionGroupPosition->position_group_id)
                )
                ->exists();

            return $isEntitledByHomeMemberRating || $isEndorsedToRating || $hasEndorsementForPositionGroup;
        };

        /** If there are a PositionGroup(s) which contain the specified position
         * perform a series of checks to determine if the account is entitled to
         * control the position */
        if ($assignedPositionGroupsWithPosition->count() > 0) {
            return $assignedPositionGroupsWithPosition->some(fn ($positionGroup) => $checkPositionForPositionGroup($positionGroup->positions->where('id', $position->id)->first()->pivot));
        }

        /** Check any unassigned position groups have a maximum atc qualification
         * if so, check if the account has a rating above the maximum specified
         * qualification and if so, they are entitled to control even if the
         * position group hasn't been endorsed to that member. */
        $unassignedPositionGroupsWithPositionWithMaxRating = $unassignedPositionGroupsWithPosition->filter(fn ($positionGroup) => isset($positionGroup->maximumAtcQualification));
        if ($unassignedPositionGroupsWithPositionWithMaxRating->count() > 0) {
            return $unassignedPositionGroupsWithPosition->some(
                function (PositionGroup $positionGroup) use ($position) {
                    $positionGroupPosition = $positionGroup->positions->where('id', $position->id)->first()->pivot;

                    return $this->account->qualification_atc->vatsim > $positionGroupPosition->positionGroup->maximumAtcQualification->vatsim;
                }
            );
        }

        $unassignedPositionGroupsWithoutMaxRating = $unassignedPositionGroupsWithPosition->filter(fn ($positionGroup) => ! isset($positionGroup->maximumAtcQualification));
        if ($unassignedPositionGroupsWithoutMaxRating->count() > 0) {
            return $unassignedPositionGroupsWithoutMaxRating->some(
                function (PositionGroup $positionGroup) use ($position) {
                    $positionGroupPosition = $positionGroup->positions->where('id', $position->id)->first()->pivot;

                    return $this->account
                        ->endorsements()
                        ->active()
                        ->whereHasMorph('endorsable',
                            PositionGroup::class,
                            fn ($query) => $query->where('id', $positionGroupPosition->position_group_id)
                        )
                        ->exists();
                }
            );
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
        // specifically given permission to control up to their rating or
        // for this specific position.
        if ($this->account->hasState('VISITING') || $this->account->hasState('TRANSFERRING')) {
            $endorsedForPosition = $this->account
                ->endorsements()
                ->active()
                ->whereHasMorph('endorsable',
                    Position::class,
                    fn ($query) => $query->where('id', $position->id)
                )
                ->exists();

            $endorsedToRating = $this->account
                ->endorsements()
                ->active()
                ->whereHasMorph('endorsable',
                    Qualification::class
                )
                ->get()
                ->sortByDesc('endorsable.vatsim')
                ->first()?->endorsable?->vatsim >= $position->getMinimumVatsimQualificationAttribute();

            return $endorsedForPosition || $endorsedToRating;
        }

        // If they are in a region or international, they cannot control
        // without one of the above conditions being met.
        if ($this->account->hasState('REGION') || $this->account->hasState('INTERNATIONAL')) {
            return false;
        }

        // They can control unrestricted up to their rating and
        // the position isn't restricted by an endorsement
        return true;
    }
}
