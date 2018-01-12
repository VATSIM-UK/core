<?php

namespace Tests\Unit;

use Tests\BrowserKitTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GroupMembershipTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itIsPossibleToJoinACommunityGroup()
    {
        $member = factory(\App\Models\Mship\Account::class)->create();
        $division = \App\Models\Mship\State::findByCode('DIVISION');
        $member->addState($division);

        $group = \App\Models\Community\Group::first();

        $member->fresh()->addCommunityGroup($group);

        $this->assertDatabaseHas('community_membership', [
            'account_id' => $member->id,
            'group_id' => $group->id,
        ]);
    }

    /** @test */
    public function itListsAllMembersInAGroup()
    {
        $memberA = factory(\App\Models\Mship\Account::class)->create();
        $memberB = factory(\App\Models\Mship\Account::class)->create();

        $division = \App\Models\Mship\State::findByCode('DIVISION');

        $memberA->addState($division);
        $memberB->addState($division);

        $group = \App\Models\Community\Group::first();

        $memberA->fresh()->addCommunityGroup($group);
        $memberB->fresh()->addCommunityGroup($group);

        $groupMembers = $group->fresh()->accounts;

        $this->assertTrue($groupMembers->contains($memberA->id));
        $this->assertTrue($groupMembers->contains($memberB->id));
    }

    /** @test */
    public function itCorrectlyDeterminesIfAMemberIsInAGroup()
    {
        $memberA = factory(\App\Models\Mship\Account::class)->create();
        $memberB = factory(\App\Models\Mship\Account::class)->create();

        $division = \App\Models\Mship\State::findByCode('DIVISION');

        $memberA->addState($division);
        $memberB->addState($division);

        $group = \App\Models\Community\Group::first();

        $memberA->fresh()->addCommunityGroup($group);
        $memberB->fresh()->addCommunityGroup($group);

        $this->assertTrue($group->fresh()->hasMember($memberA));
        $this->assertTrue($group->fresh()->hasMember($memberB));
    }

    /** @test */
    public function itIsNotPossibleToJoinACommunityGroupAsANonDivisionMember()
    {
        $this->expectException(\App\Exceptions\Community\MustBeADivisionMemberException::class);

        $member = factory(\App\Models\Mship\Account::class)->create();
        $international = \App\Models\Mship\State::findByCode('INTERNATIONAL');
        $member->addState($international);

        $defaultGroup = \App\Models\Community\Group::isDefault()->first();

        $member->fresh()->addCommunityGroup($defaultGroup);
    }

    /** @test */
    public function itIsPossibleToJoinMultipleGroupsAcrossTiers()
    {
        $member = factory(\App\Models\Mship\Account::class)->create();
        $divisionState = \App\Models\Mship\State::findByCode('DIVISION');
        $member->addState($divisionState);

        $tier1 = \App\Models\Community\Group::inTier(1)->first();
        $tier2 = \App\Models\Community\Group::inTier(2)->first();

        $member->fresh()->addCommunityGroup($tier1);
        $member->fresh()->addCommunityGroup($tier2);
    }

    /** @test */
    public function itIsNotPossibleToJoinTheSameGroupTwice()
    {
        $this->expectException(\App\Exceptions\Community\AlreadyAGroupTierMemberException::class);

        $member = factory(\App\Models\Mship\Account::class)->create();
        $divisionState = \App\Models\Mship\State::findByCode('DIVISION');
        $member->addState($divisionState);

        $defaultGroup = \App\Models\Community\Group::isDefault()->first();

        $member->fresh()->addCommunityGroup($defaultGroup);
        $member->fresh()->addCommunityGroup($defaultGroup);
    }

    /** @test */
    public function itIsNotPossibleToJoinMoreThanOneGroupFromTheSameTier()
    {
        $this->expectException(\App\Exceptions\Community\AlreadyAGroupTierMemberException::class);

        $member = factory(\App\Models\Mship\Account::class)->create();
        $divisionState = \App\Models\Mship\State::findByCode('DIVISION');
        $member->addState($divisionState);

        $tier2A = \App\Models\Community\Group::inTier(2)->first();
        $tier2B = \App\Models\Community\Group::inTier(2)
            ->where('id', '!=', $tier2A->id)
            ->first();

        $member->fresh()->addCommunityGroup($tier2A);
        $member->fresh()->addCommunityGroup($tier2B);
    }
}
