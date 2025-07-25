<?php

namespace Tests\Feature\Account;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function test_user_can_request_password_reset()
    {
        $this->actingAs($this->user, 'vatsim-sso')
            ->get(route('auth-secondary'));

        $this->actingAs($this->user, 'vatsim-sso')
            ->post(route('password.email'))
            ->assertRedirect(route('auth-secondary'))
            ->assertSessionHas('status');
    }

    #[Test]
    public function test_password_reset_updates_correctly()
    {
        $this->user->password = 'Testing123';

        $now = Carbon::today();
        Carbon::setTestNow($now);

        // Check the user can visit the reset page
        $token = Password::broker()->createToken($this->user);
        $this->actingAs($this->user, 'vatsim-sso')
            ->get(route('password.reset', $token))
            ->assertSuccessful();

        // Hold time to allow for comparision

        // Reset the password
        $this->followingRedirects()->actingAs($this->user, 'vatsim-sso')
            ->from(route('password.reset', $token))
            ->post(route('password.request'), ['token' => $token, 'password' => 'Testing234', 'password_confirmation' => 'Testing234'])
            ->assertSuccessful();

        $this->assertTrue(Hash::check('Testing234', $this->user->fresh()->password));
        $this->assertEquals($now, $this->user->fresh()->password_set_at);
        $this->assertEquals($now->addDays($this->user->roles()->first()->password_lifetime), $this->user->fresh()->password_expires_at);
    }
}
