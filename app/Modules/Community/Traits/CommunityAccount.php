<?php

namespace App\Modules\Community\Traits;

use App\Modules\Community\Models\Group;
use App\Modules\Community\Exceptions\Membership\AlreadyAGroupTierMemberException;

trait CommunityAccount
{
    /**
     * Fetch all community group memberships.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function communityGroups()
    {
        return $this->belongsToMany(\App\Modules\Community\Models\Group::class, 'community_membership',
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
    public function addCommunityGroup(\App\Modules\Community\Models\Group $group)
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
            throw new \App\Modules\Community\Exceptions\Membership\MustBeADivisionMemberException($this);
        }
    }

    private function guestAgainstMultipleMembershipsToSameTier(\App\Modules\Community\Models\Group $group)
    {
        $sameTier = $this->communityGroups->filter(function ($filteredGroup) use ($group) {
            return $filteredGroup->tier == $group->tier;
        });

        if ($sameTier->count() > 0) {
            throw new \App\Modules\Community\Exceptions\Membership\AlreadyAGroupTierMemberException($this, $group);
        }
    }
}
