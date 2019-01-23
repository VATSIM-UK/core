<?php

namespace Tests\Feature\Mship;

use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use DatabaseTransactions;

    /** @test **/
    public function testUserCanRequestPasswordReset()
    {
        $user = factory(Account::class)->create();

        $this->actingAs($user, 'vatsim-sso')
            ->get(route('auth-secondary'));

        $this->actingAs($user, 'vatsim-sso')
            ->post(route('password.email'))
            ->assertRedirect(route('auth-secondary'))
            ->assertSessionHas('status');
    }

    /** @test **/
    public function testPasswordResetUpdatesCorrectly()
    {
        $user = factory(Account::class)->create(['password' => 'Testing123']);
        $token = Password::broker()->createToken($user);
        $this->actingAs($user, 'vatsim-sso')
            ->get(route('password.reset', $token))
            ->assertSuccessful();

        Carbon::setTestNow(Carbon::now()); // Fix time to allow comparison
        $this->followingRedirects()->actingAs($user, 'vatsim-sso')
            ->from(route('password.reset', $token))
            ->post(route('password.request'), ['token' => $token, 'password' => 'Testing234', 'password_confirmation' => 'Testing234'])
            ->assertSuccessful();

        $this->assertTrue(Hash::check('Testing234', $user->fresh()->password));
        $this->assertEquals(Carbon::now(), $user->fresh()->password_set_at);
        $this->assertEquals(Carbon::now()->addDays($user->roles()->first()->password_lifetime), $user->fresh()->password_expires_at);
    }
}
