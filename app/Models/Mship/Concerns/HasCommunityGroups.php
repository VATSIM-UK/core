<?php

namespace App\Models\Mship\Concerns;

use App\Exceptions\Community\AlreadyAGroupTierMemberException;
use App\Models\Community\Group;

trait HasCommunityGroups
{
    /**
     * Fetch all community group memberships.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function communityGroups()
    {
        return $this->belongsToMany(\App\Models\Community\Group::class, 'community_membership',
            'account_id', 'group_id')
            ->withTimestamps()
            ->withPivot(['created_at', 'updated_at', 'deleted_at'])
            ->wherePivot('deleted_at', null);
    }

    /**
     * Add a user to the provided community group.
     *
     * @return void
     */
    public function addCommunityGroup(\App\Models\Community\Group $group)
    {
        $this->guardAgainstNonDivisionJoiningACommunityGroup();
        $this->guestAgainstMultipleMembershipsToSameTier($group);

        $this->communityGroups()->save($group);
    }

    public function syncWithDefaultCommunityGroup()
    {
        try {
            $defaultGroup = Group::isDefault()->first();
            $this->addCommunityGroup($defaultGroup);

            return true;
        } catch (AlreadyAGroupTierMemberException $ex) {
            return false;
        }
    }

    private function guardAgainstNonDivisionJoiningACommunityGroup()
    {
        if (!$this->hasState('DIVISION')) {
            throw new \App\Exceptions\Community\MustBeADivisionMemberException($this);
        }
    }

    private function guestAgainstMultipleMembershipsToSameTier(\App\Models\Community\Group $group)
    {
        $sameTier = $this->communityGroups->filter(function ($filteredGroup) use ($group) {
            return $filteredGroup->tier == $group->tier;
        });

        if ($sameTier->count() > 0) {
            throw new \App\Exceptions\Community\AlreadyAGroupTierMemberException($this, $group);
        }
    }
}
