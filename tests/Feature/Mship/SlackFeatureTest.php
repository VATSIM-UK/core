<?php

namespace Tests\Feature\Mship;

use App\Models\Mship\Account;
use App\Models\Mship\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlackFeatureTest extends TestCase
{
    use RefreshDatabase;

    private $account;

    protected function setUp()
    {
        parent::setUp();

        $this->account = factory(Account::class)->create(['slack_id' => '123456789']);
    }

    /** @test **/
    public function testRedirectIfRegistrationAlreadyExists()
    {
        $this->account->addState(State::find(3));

        $this->actingAs($this->account)->get(route('slack.new'))->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('error',
                'You already have a Slack registration with this account. Please contact the Web Services Department if you believe this to be an error.');
    }

    /** @test **/
    public function testRedirectIfInternationalMember()
    {
        $account = factory(Account::class)->create();
        $account->addState(State::find(5), 'USA', 'ZLA'); // TODO: Add International member state

        $this->actingAs($account)->get(route('slack.new'))->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('error',
                'Your account is listed as an International Member, whom are not permitted to register for slack. Please contact the Web Services Department if you believe this to be an error.');
    }
}
