<?php

namespace Tests\Feature\Community;

use App\Models\Community\Group;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DeploymentTest extends TestCase
{
    use DatabaseTransactions;

    public $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = factory(\App\Models\Mship\Account::class)->create();
        $this->account->addState(\App\Models\Mship\State::findByCode('DIVISION'));
        $this->account = $this->account->fresh();
    }

    /** @test * */
    public function testDivisionUserCanDeploy()
    {
        $this->actingAs($this->account)->get(route('community.membership.deploy'))->assertSuccessful();
        $this->followingRedirects()->actingAs($this->account)->post(route('community.membership.deploy.post'), ['group' => 1])->assertSuccessful();
    }

    /** @test * */
    public function testDivisionUserCantDeployTwice()
    {
        $this->account->addCommunityGroup(Group::find(1));
        $this->actingAs($this->account)->post(route('community.membership.deploy.post'), ['group' => 1])->assertForbidden();
    }

    /** @test * */
    public function testDivisionUserCantDeployTwiceIntoUKCommunity()
    {
        $group = Group::find(1);
        $account = factory(Account::class)->create();
        $this->followingRedirects()->actingAs($this->account)->post(route('community.membership.deploy.post'), ['group' => 1])->assertSuccessful();
        $this->followingRedirects()->actingAs($this->account)->post(route('community.membership.deploy.post'), ['group' => 1])->assertForbidden();

        $this->assertEquals(1, $account->fresh()->communityGroups()->count());
    }

    /** @test * */
    public function testDivisionUserCanDeployWhenOnlyInDefaultGroup()
    {
        $this->account->addCommunityGroup(Group::find(1));
        $this->actingAs($this->account)->get(route('community.membership.deploy'))->assertSuccessful();
    }

    /** @test * */
    public function testDivisionUserCantDeployWithMoreThanOneNonDefaultGroup()
    {
        $this->account->addCommunityGroup(Group::find(1));
        $this->account->addCommunityGroup(Group::find(2));
        $this->actingAs($this->account)->get(route('community.membership.deploy'))->assertForbidden();
    }
}
