<?php

namespace Tests\Feature\Community;

use App\Models\Community\Group;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DeploymentTest extends TestCase
{
    use DatabaseTransactions;

    public $divisionUser;

    public function setUp()
    {
        parent::setUp();

        $this->divisionUser = factory(\App\Models\Mship\Account::class)->create();
        $this->divisionUser->addState(\App\Models\Mship\State::findByCode('DIVISION'));
        $this->divisionUser = $this->divisionUser->fresh();
    }

    /** @test */
    public function testDivisionUserCanDeploy()
    {
        $this->actingAs($this->divisionUser)
            ->get(route('community.membership.deploy'))
            ->assertSuccessful();

        $this->followingRedirects()->actingAs($this->divisionUser)
            ->post(route('community.membership.deploy.post'), ['group' => 1])
            ->assertSuccessful();
    }

    /** @test */
    public function testDivisionUserCantDeployTwice()
    {
        // Add to the UK Community
        $this->divisionUser->addCommunityGroup(Group::find(1));

        $this->actingAs($this->divisionUser)
            ->post(route('community.membership.deploy.post'), ['group' => 1])
            ->assertForbidden();
    }

    /** @test */
    public function testDivisionUserCanDeployWhenOnlyInDefaultGroup()
    {
        // Add to the UK Community
        $this->divisionUser->addCommunityGroup(Group::find(1));
        $this->actingAs($this->divisionUser)
            ->get(route('community.membership.deploy'))
            ->assertSuccessful();
    }

    /** @test */
    public function testDivisionUserCantDeployWithMoreThanOneNonDefaultGroup()
    {
        // Add to the UK Community & a group
        $this->divisionUser->addCommunityGroup(Group::find(1));
        $this->divisionUser->addCommunityGroup(Group::find(2));

        $this->actingAs($this->divisionUser)
            ->get(route('community.membership.deploy'))
            ->assertForbidden();
    }
}
