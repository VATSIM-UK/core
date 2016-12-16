<?php

namespace App\Modules\Community\Traits;

use App\Modules\Community\Models\Group;
use App\Modules\Community\Exceptions\Membership\AlreadyAGroupMemberException;

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

        $this->guardAgainstDoubleJoiningACommunityGroup($group);

        $this->communityGroups()->save($group);
    }

    public function syncWithDefaultCommunityGroup()
    {
        try {
            $defaultGroup = Group::isDefault()->first();
            $this->addCommunityGroup($defaultGroup);

            return true;
        } catch (AlreadyAGroupMemberException $ex) {
            return false;
        }
    }

    private function guardAgainstNonDivisionJoiningACommunityGroup()
    {
        if (! $this->hasState('DIVISION')) {
            throw new \App\Modules\Community\Exceptions\Membership\MustBeADivisionMemberException($this);
        }
    }

    private function guardAgainstDoubleJoiningACommunityGroup(\App\Modules\Community\Models\Group $group)
    {
        if ($this->communityGroups->contains($group)) {
            throw new \App\Modules\Community\Exceptions\Membership\AlreadyAGroupMemberException($this, $group);
        }
    }
}
