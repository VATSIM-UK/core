<?php

namespace App\Models\Atc;

use App\Models\Atc\Endorsement\Condition;
use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Model;

class Endorsement extends Model
{
    protected $table = 'endorsements';
    protected $fillable = [
        'name',
    ];

    private $allMet;

    public function conditions()
    {
        return $this->hasMany(Condition::class);
    }

    public function conditionsMetForUser(Account $user)
    {
        if($this->allMet){
            return $this->allMet;
        }

        return $this->allMet = $this->conditions->filter(function ($condition) use ($user){
            return !$condition->isMetForUser($user);
        })->count() == 0;
    }
}
