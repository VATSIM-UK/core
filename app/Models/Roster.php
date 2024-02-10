<?php

namespace App\Models;

use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Atc\PositionGroupPosition;
use App\Models\Mship\Account;
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

    protected static function booted(): void
    {
        // TODO: Will need to check those visiting/transferring
        // have been given permission to be on the roster

        // Only return users that are on the roster
        // and are still within the UK.
        static::addGlobalScope('eligibleState', function (Builder $builder) {
            $builder->whereHas('account', function ($query) {
                $query->whereHas('states', function ($query) {
                    $query->whereIn('mship_state.code', ['DIVISION', 'VISITING', 'TRANSFERRING']);
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

        // If the position is part of a group, do they have
        // the endorsement for that group?
        if($positionGroupPosition = PositionGroupPosition::where('position_id', $position->id)->first()) {
            return $this->account
                ->endorsements()
                ->active()
                ->whereHasMorph('endorsable',
                    PositionGroup::class,
                    fn($query) => $query->where('id', $positionGroupPosition->position_group_id)
                )
                ->exists();
        }

        // If the position is above their rating, do they
        // have an active solo endorsement?
        if($position->type > $this->account->qualification_atc) {
            return $this->account
                ->endorsements()
                ->active()
                ->whereHasMorph('endorsable',
                    Position::class,
                    fn($query) => $query->where('id', $position->id)
                )
                ->exists();
        }

        // If they are not a home member of our division, they need to have been
        // specifically given permission to control up to their rating
        if ($this->account->primary_permanent_state->code != 'DIVISION') {
            // TODO: Some sort of setting from admin panel to say that this person can control
            // up to their rating
        }

        // They can control unrestricted up to their rating and
        // the position isn't restricted by an endorsement
        return true;
    }
}
