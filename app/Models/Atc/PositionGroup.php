<?php

namespace App\Models\Atc;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PositionGroup extends Model
{
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
}
