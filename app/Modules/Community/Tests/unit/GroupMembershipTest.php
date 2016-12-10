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
}
