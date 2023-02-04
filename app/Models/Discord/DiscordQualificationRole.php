<?php

namespace App\Models\Discord;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscordQualificationRole extends Model
{
    use HasFactory;

    public $timestamps = false;

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

    /**
     * Determine if account satifies the requirements for the role.
     *
     * @param  Account  $account
     * @return bool
     */
    public function accountSatisfies(Account $account): bool
    {
        return $account->hasQualification($this->qualification) && (! $this->state || $account->hasState($this->state));
    }
}
