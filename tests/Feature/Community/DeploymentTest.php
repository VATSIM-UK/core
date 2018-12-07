<?php

    namespace Tests\Feature\Community;

    use App\Models\Community\Group;
    use Illuminate\Foundation\Testing\DatabaseTransactions;
    use Illuminate\Support\Facades\Auth;
    use Tests\TestCase;

    class DeploymentTest extends TestCase
    {
        use DatabaseTransactions;

        var $account;

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
