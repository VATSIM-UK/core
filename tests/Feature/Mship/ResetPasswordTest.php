<?php

namespace Tests\Feature\Mship;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use DatabaseTransactions;

    /** @test **/
    public function a_user_can_reset_their_password()
    {
        $this->withoutExceptionHandling();

        $user = factory(Account::class)->create();

        $this->actingAs($user, 'vatsim-sso')
            ->post(route('password.email'))
            ->assertRedirect()
            ->assertSessionHas('status');
    }
}
