<?php

namespace Tests\Feature\Mship;

use App\Models\Mship\Account;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        $this->actingAs($this->account)->get(route('slack.new'))->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('error',
                'You already have a Slack registration with this account. Please contact the Web Services Department if you believe this to be an error.');
    }
}
