<?php

namespace App\Models\Mship\Concerns;

use App\Events\Mship\AccountAltered;
use App\Exceptions\Mship\InvalidStateException;
use App\Models\Mship\AccountState;
use App\Models\Mship\State;
use Carbon\Carbon;

trait HasStates
{
    /**
     * Return all active related states for this account.
     *
     * @return mixed
     */
    public function states()
    {
        return $this->belongsToMany(State::class, 'mship_account_state', 'account_id', 'state_id')
            ->withPivot(['region', 'division', 'start_at', 'end_at'])
            ->wherePivot('end_at', null)
            ->using(AccountState::class);
    }

    /**
     * Return all related states for this account.
     *
     * @return mixed
     */
    public function statesHistory()
    {
        return $this->belongsToMany(State::class, 'mship_account_state', 'account_id', 'state_id')
            ->withPivot(['region', 'division', 'start_at', 'end_at'])
            ->orderBy('pivot_start_at', 'DESC')
            ->using(AccountState::class);
    }

    /**
     * Check whether the user has the given state presently.
     *
     * @param  string|State  $search  The given state to check if the account has.
     * @return bool
     *
     * @throws InvalidStateException
     */
    public function hasState($search)
    {
        if (is_string($search)) {
            return $this->states->contains(function ($state) use ($search) {
                return strcasecmp($state->code, $search) === 0;
            });
        } elseif ($search instanceof State) {
            return $this->states->contains('id', $search->id);
        } else {
            throw new InvalidStateException;
        }
    }

    /**
     * Update the member's region and division.
     *
     * @param  string  $division  Division code as reported by VATSIM.
     * @param  string  $region  Region code as reported by VATSIM.
     */
    public function updateDivision($division, $region)
    {
        $state = determine_mship_state_from_vatsim($region, $division);
        $this->addState($state, $region, $division);
    }

    /**
     * Laravel magic-getter - return the primary state;.
     *
     * @return mixed
     */
    public function getPrimaryStateAttribute()
    {
        return $this->states->sortBy('priority')->first();
    }

    /**
     * Laravel magic-getter - return the primary permanent state.
     *
     * @return mixed
     */
    public function getPrimaryPermanentStateAttribute()
    {
        return $this->states->where('type', 'perm')->sortBy('priority')->first();
    }

    /**
     * Get all temporary states.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTemporaryStatesAttribute()
    {
        return $this->states()->temporary()->get();
    }

    /**
     * Get all permanent states.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPermanentStatesAttribute()
    {
        return $this->states()->permanent()->get();
    }

    /**
     * Set the account's current state to the given value.
     *
     * @param  State  $state  The state to set.
     * @param  string|null  $region  Member's region
     * @param  string|null  $division  Member's division
     * @return mixed
     *
     * @throws \App\Exceptions\Mship\InvalidStateException
     */
    public function addState(State $state, $region = null, $division = null)
    {
        // Cleanup Old States
        $permanentStates = $this->states->sortByDesc('pivot.start_at')->filter(function ($state) {
            return $state->isPermanent;
        });
        if ($permanentStates->count() > 1) {
            // They have more than 1 permanent state? Let's set all but the latest to ended...
            $this->states()->permanent()->wherePivot('id', '!=', $permanentStates->first()->pivot->id)->update(['end_at' => Carbon::now()]);
        }
        if ($this->fresh()->hasState($state)) {
            // Already has same class of state (e.g Intl)
            // Verify the same region/division information, else we want to update the state
            $exisitingState = $this->fresh()->states->sortByDesc('pivot.start_at')->where('id', $state->id)->first();
            if ($exisitingState->pivot->region == $region && $exisitingState->pivot->division == $division) {
                return;
            }
        }

        // New state
        if ($this->primary_permanent_state && $state->is_permanent) {
            // New state is a permanent one, so lets remove the old permanent state
            $this->removeState($this->primary_permanent_state);
        }

        if ($state->delete_all_temps) {
            $this->temporary_states->map(function ($tempState) {
                $this->removeState($tempState);
            });
        }

        $state = $this->states()->attach($state, [
            'start_at' => Carbon::now(),
            'region' => $region,
            'division' => $division,
        ]);

        $this->touch();
        event(new AccountAltered($this));

        return $state;
    }

    public function removeState(State $state)
    {
        $update = $this->states()->updateExistingPivot($state->id, [
            'end_at' => Carbon::now(),
        ]);
        event(new AccountAltered($this));

        return $update;
    }
}
