<?php

namespace Tests\Feature\UKCP;

use App\Libraries\UKCP;
use App\Models\Mship\Account;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TokenInvalidationTest extends TestCase
{
    #[Test]
    public function invalidating_tokens_requires_a_state_changing_request()
    {
        $this->mock(UKCP::class)->shouldNotReceive('deleteToken');

        $this->actingAs(Account::factory()->create())
            ->get(route('ukcp.token.invalidate'))
            ->assertRedirect(route('landing'));
    }

    #[Test]
    public function a_member_can_invalidate_their_tokens()
    {
        $this->mock(UKCP::class)
            ->shouldReceive('getValidTokensFor')->once()->andReturn(collect([(object) ['id' => 'token-id']]))
            ->shouldReceive('deleteToken')->once()->with('token-id', \Mockery::type(Account::class));

        $this->actingAs(Account::factory()->create())
            ->post(route('ukcp.token.invalidate'))
            ->assertRedirect(route('ukcp.guide'));
    }
}
