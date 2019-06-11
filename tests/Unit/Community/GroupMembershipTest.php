<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GroupMembershipTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itIsPossibleToJoinACommunityGroup()
    {
        $user = factory(\App\Models\Mship\Account::class)->create();
        $division = \App\Models\Mship\State::findByCode('DIVISION');
        $user->addState($division);

        $group = \App\Models\Community\Group::first();

        $user->fresh()->addCommunityGroup($group);

        $this->assertDatabaseHas('community_membership', [
            'account_id' => $user->id,
            'group_id' => $group->id,
        ]);
    }

    /** @test */
    public function itListsAllMembersInAGroup()
    {
        $userA = factory(\App\Models\Mship\Account::class)->create();
        $userB = factory(\App\Models\Mship\Account::class)->create();

        $division = \App\Models\Mship\State::findByCode('DIVISION');

        $userA->addState($division);
        $userB->addState($division);

        $group = \App\Models\Community\Group::first();

        $userA->fresh()->addCommunityGroup($group);
        $userB->fresh()->addCommunityGroup($group);

        $groupMembers = $group->fresh()->accounts;

        $this->assertTrue($groupMembers->contains($userA->id));
        $this->assertTrue($groupMembers->contains($userB->id));
    }

    /** @test */
    public function itCorrectlyDeterminesIfAMemberIsInAGroup()
    {
        $userA = factory(\App\Models\Mship\Account::class)->create();
        $userB = factory(\App\Models\Mship\Account::class)->create();

        $division = \App\Models\Mship\State::findByCode('DIVISION');

        $userA->addState($division);
        $userB->addState($division);

        $group = \App\Models\Community\Group::first();

        $userA->fresh()->addCommunityGroup($group);
        $userB->fresh()->addCommunityGroup($group);

        $this->assertTrue($group->fresh()->hasMember($userA));
        $this->assertTrue($group->fresh()->hasMember($userB));
    }

    /** @test */
    public function itIsNotPossibleToJoinACommunityGroupAsANonDivisionMember()
    {
        $this->expectException(\App\Exceptions\Community\MustBeADivisionMemberException::class);

        $user = factory(\App\Models\Mship\Account::class)->create();
        $international = \App\Models\Mship\State::findByCode('INTERNATIONAL');
        $user->addState($international);

        $defaultGroup = \App\Models\Community\Group::isDefault()->first();

        $user->fresh()->addCommunityGroup($defaultGroup);
    }

    /** @test */
    public function itIsPossibleToJoinMultipleGroupsAcrossTiers()
    {
        $user = factory(\App\Models\Mship\Account::class)->create();
        $divisionState = \App\Models\Mship\State::findByCode('DIVISION');
        $user->addState($divisionState);

        $tier1 = \App\Models\Community\Group::inTier(1)->first();
        $tier2 = \App\Models\Community\Group::inTier(2)->first();

        $user->fresh()->addCommunityGroup($tier1);
        $user->fresh()->addCommunityGroup($tier2);

        $this->assertTrue($user->fresh()->communityGroups->contains($tier1) && $user->fresh()->communityGroups->contains($tier2));
    }

    /** @test */
    public function itIsNotPossibleToJoinTheSameGroupTwice()
    {
        $this->expectException(\App\Exceptions\Community\AlreadyAGroupTierMemberException::class);

        $user = factory(\App\Models\Mship\Account::class)->create();
        $divisionState = \App\Models\Mship\State::findByCode('DIVISION');
        $user->addState($divisionState);

        $defaultGroup = \App\Models\Community\Group::isDefault()->first();

        $user->fresh()->addCommunityGroup($defaultGroup);
        $user->fresh()->addCommunityGroup($defaultGroup);
    }

    /** @test */
    public function itIsNotPossibleToJoinMoreThanOneGroupFromTheSameTier()
    {
        $this->expectException(\App\Exceptions\Community\AlreadyAGroupTierMemberException::class);

        $user = factory(\App\Models\Mship\Account::class)->create();
        $divisionState = \App\Models\Mship\State::findByCode('DIVISION');
        $user->addState($divisionState);

        $tier2A = \App\Models\Community\Group::inTier(2)->first();
        $tier2B = \App\Models\Community\Group::inTier(2)
            ->where('id', '!=', $tier2A->id)
            ->first();

        $user->fresh()->addCommunityGroup($tier2A);
        $user->fresh()->addCommunityGroup($tier2B);
    }
}
