<?php

namespace App\Modules\Community\Traits;

trait CommunityAccount
{
    /**
     * Fetch all community group memberships.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function communityGroups()
    {
        return $this->belongsToMany(\App\Modules\Community\Models\Group::class, 'community_membership', 'group_id',
            'account_id')
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

        $this->guardAgainstDoubleJoiningACommunityGroup($this, $group);

        $this->communityGroups()->save($group);
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
