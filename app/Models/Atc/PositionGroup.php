<?php

namespace App\Models\Atc;

use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement as MshipEndorsement;
use App\Models\Mship\Qualification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PositionGroup extends Model implements Endorseable
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function conditions()
    {
        return $this->hasMany(PositionGroupCondition::class);
    }

    public function positions()
    {
        return $this->belongsToMany(Position::class, 'position_group_positions', 'position_group_id', 'position_id');
    }

    public function endorsement()
    {
        return $this->belongsTo(Endorsement::class);
    }

    public function maximumAtcQualification()
    {
        return $this->belongsTo(Qualification::class);
    }

    public function membershipEndorsement()
    {
        return $this->morphMany(MshipEndorsement::class, 'endorsable');
    }

    public function conditionsMetForUser(Account $account): bool
    {
        $cacheKey = $this->generateCacheKey($account);
        $cacheTtl = 86400; // 24 hours

        if (Cache::has($cacheKey)) {
            return (bool) Cache::get($cacheKey);
        }

        // check if every condition for the endorsement has been fulfilled
        $allMet = $this->conditions->every(function ($condition) use (&$account) {
            return $condition->isMetForUser($account);
        });

        // cache the result of whether or not the conditions have been met
        Cache::put($cacheKey, $allMet, $cacheTtl);

        return $allMet;
    }

    public function generateCacheKey(Account $account)
    {
        return "endorsement:{$this->id}:account:{$account->id}:met";
    }

    public static function unassignedFor(Account $account)
    {
        return self::all()->reject(function ($positionGroup) use (&$account) {
            $nonExpiredEndorsements = $account->load('endorsements')->endorsements->reject(function ($endorsement) {
                return $endorsement->hasExpired();
            });

            $positionGroupsAssigned = $nonExpiredEndorsements->contains(function ($value, $key) use (&$positionGroup) {
                return $value->endorsable_id == $positionGroup->id
                    && $value->endorsable_type == PositionGroup::class;
            });

            return $positionGroupsAssigned;
        });
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return implode(', ', $this->positions->map(
            fn ($position) => $position->callsign
        )->toArray());
    }
}
