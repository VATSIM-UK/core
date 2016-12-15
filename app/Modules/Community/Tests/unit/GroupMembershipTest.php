<?php


use Illuminate\Foundation\Testing\DatabaseTransactions;

class GroupMembershipTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_is_not_possible_to_join_a_community_group_as_a_non_division_member()
    {
        $this->setExpectedException(\App\Modules\Community\Exceptions\Membership\MustBeADivisionMemberException::class);

        $member        = factory(\App\Models\Mship\Account::class)->create();
        $international = \App\Models\Mship\State::findByCode('INTERNATIONAL');
        $member->addState($international);

        $defaultGroup = \App\Modules\Community\Models\Group::isDefault()->first();

        $member->addCommunityGroup($defaultGroup);
    }

    /** @test */
    public function it_is_not_possible_to_join_the_same_group_twice(){
        $this->setExpectedException(\App\Modules\Community\Exceptions\Membership\AlreadyAGroupMemberException::class);

        $member        = factory(\App\Models\Mship\Account::class)->create();
        $divisionState = \App\Models\Mship\State::findByCode('DIVISION');
        $member->addState($divisionState);

        $defaultGroup = \App\Modules\Community\Models\Group::isDefault()->first();

        $member->addCommunityGroup($defaultGroup);
        $member->addCommunityGroup($defaultGroup);
    }
}
