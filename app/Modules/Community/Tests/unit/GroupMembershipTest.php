<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GroupMembershipTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_is_possible_to_join_a_community_group()
    {
        $member = factory(\App\Models\Mship\Account::class)->create();
        $division = \App\Models\Mship\State::findByCode('DIVISION');
        $member->addState($division);

        $group = \App\Modules\Community\Models\Group::first();

        $member->fresh()->addCommunityGroup($group);

        $this->seeInDatabase('community_membership', [
            'account_id' => $member->id,
            'group_id' => $group->id,
        ]);
    }

    /** @test */
    public function it_lists_all_members_in_a_group()
    {
        $memberA = factory(\App\Models\Mship\Account::class)->create();
        $memberB = factory(\App\Models\Mship\Account::class)->create();

        $division = \App\Models\Mship\State::findByCode('DIVISION');

        $memberA->addState($division);
        $memberB->addState($division);

        $group = \App\Modules\Community\Models\Group::first();

        $memberA->fresh()->addCommunityGroup($group);
        $memberB->fresh()->addCommunityGroup($group);

        $groupMembers = $group->fresh()->accounts;

        $this->assertTrue($groupMembers->contains($memberA->id));
        $this->assertTrue($groupMembers->contains($memberB->id));
    }

    /** @test */
    public function it_correctly_determines_if_a_member_is_in_a_group()
    {
        $memberA = factory(\App\Models\Mship\Account::class)->create();
        $memberB = factory(\App\Models\Mship\Account::class)->create();

        $division = \App\Models\Mship\State::findByCode('DIVISION');

        $memberA->addState($division);
        $memberB->addState($division);

        $group = \App\Modules\Community\Models\Group::first();

        $memberA->fresh()->addCommunityGroup($group);
        $memberB->fresh()->addCommunityGroup($group);

        $this->assertTrue($group->fresh()->hasMember($memberA));
        $this->assertTrue($group->fresh()->hasMember($memberB));
    }

    /** @test */
    public function it_is_not_possible_to_join_a_community_group_as_a_non_division_member()
    {
        $this->setExpectedException(\App\Modules\Community\Exceptions\Membership\MustBeADivisionMemberException::class);

        $member = factory(\App\Models\Mship\Account::class)->create();
        $international = \App\Models\Mship\State::findByCode('INTERNATIONAL');
        $member->addState($international);

        $defaultGroup = \App\Modules\Community\Models\Group::isDefault()->first();

        $member->fresh()->addCommunityGroup($defaultGroup);
    }

    /** @test */
    public function it_is_possible_to_join_multiple_groups_across_tiers()
    {
        $member = factory(\App\Models\Mship\Account::class)->create();
        $divisionState = \App\Models\Mship\State::findByCode('DIVISION');
        $member->addState($divisionState);

        $tier1 = \App\Modules\Community\Models\Group::inTier(1)->first();
        $tier2 = \App\Modules\Community\Models\Group::inTier(2)->first();

        $member->fresh()->addCommunityGroup($tier1);
        $member->fresh()->addCommunityGroup($tier2);
    }

    /** @test */
    public function it_is_not_possible_to_join_the_same_group_twice()
    {
        $this->setExpectedException(\App\Modules\Community\Exceptions\Membership\AlreadyAGroupTierMemberException::class);

        $member = factory(\App\Models\Mship\Account::class)->create();
        $divisionState = \App\Models\Mship\State::findByCode('DIVISION');
        $member->addState($divisionState);

        $defaultGroup = \App\Modules\Community\Models\Group::isDefault()->first();

        $member->fresh()->addCommunityGroup($defaultGroup);
        $member->fresh()->addCommunityGroup($defaultGroup);
    }

    /** @test */
    public function it_is_not_possible_to_join_more_than_one_group_from_the_same_tier()
    {
        $this->setExpectedException(\App\Modules\Community\Exceptions\Membership\AlreadyAGroupTierMemberException::class);

        $member = factory(\App\Models\Mship\Account::class)->create();
        $divisionState = \App\Models\Mship\State::findByCode('DIVISION');
        $member->addState($divisionState);

        $tier2A = \App\Modules\Community\Models\Group::inTier(2)->first();
        $tier2B = \App\Modules\Community\Models\Group::inTier(2)
                                                     ->where('id', '!=', $tier2A->id)
                                                     ->first();

        $member->fresh()->addCommunityGroup($tier2A);
        $member->fresh()->addCommunityGroup($tier2B);
    }
}
