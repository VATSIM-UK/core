<?php

namespace Tests\Feature\Account;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function testUserCanRequestPasswordReset()
    {
        $this->actingAs($this->user, 'vatsim-sso')
            ->get(route('auth-secondary'));

        $this->actingAs($this->user, 'vatsim-sso')
            ->post(route('password.email'))
            ->assertRedirect(route('auth-secondary'))
            ->assertSessionHas('status');
    }

    /** @test */
    public function testPasswordResetUpdatesCorrectly()
    {
        $this->user->password = 'Testing123';

        // Check the user can visit the reset page
        $token = Password::broker()->createToken($this->user);
        $this->actingAs($this->user, 'vatsim-sso')
            ->get(route('password.reset', $token))
            ->assertSuccessful();

        // Hold time to allow for comparision
        Carbon::setTestNow(Carbon::now());

        // Reset the password
        $this->followingRedirects()->actingAs($this->user, 'vatsim-sso')
            ->from(route('password.reset', $token))
            ->post(route('password.request'), ['token' => $token, 'password' => 'Testing234', 'password_confirmation' => 'Testing234'])
            ->assertSuccessful();

        $this->assertTrue(Hash::check('Testing234', $this->user->fresh()->password));
        $this->assertEquals(Carbon::now(), $this->user->fresh()->password_set_at);
        $this->assertEquals(Carbon::now()->addDays($this->user->roles()->first()->password_lifetime), $this->user->fresh()->password_expires_at);
    }
}
