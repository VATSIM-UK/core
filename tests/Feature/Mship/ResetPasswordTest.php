<?php

namespace Tests\Feature\Mship;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use DatabaseTransactions;

    /** @test **/
    public function a_user_can_reset_their_password()
    {
        $user = factory(Account::class)->create();

        $this->actingAs($user, 'vatsim-sso')
            ->get(route('auth-secondary'));

        $this->post(route('password.email'))
            ->assertRedirect(route('auth-secondary'))
            ->assertSessionHas('status')
            ->assertSessionDoesntHaveErrors();
    }
}
