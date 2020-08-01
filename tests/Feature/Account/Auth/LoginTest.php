<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\SecondaryLoginController;
use App\Models\Mship\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function testItDirectsToLogin()
    {
        $this->assertGuest();
        $this->get(route('login'))
            ->assertRedirect();
        $this->post(route('login'))
            ->assertRedirect();
    }

    public function testItRedirectsWithoutVatsimSSOOnSecondaryLogin()
    {
        $this->assertFalse(Auth::guard('vatsim-sso')->check());
        $this->post(route('auth-secondary'))
            ->assertRedirect(route('login'));
        $this->get(route('auth-secondary'))
            ->assertRedirect(route('login'));
    }

    public function testItAllowsLoginWithoutSecondaryPassword()
    {
        $user = factory(Account::class)->create();
        $this->assertFalse($user->hasPassword());

        Auth::guard('vatsim-sso')->login($user);
        $response = SecondaryLoginController::attemptSecondaryAuth($user);

        $this->assertTrue($response->isRedirect());
        $this->assertEquals(route('site.home'), $response->getTargetUrl());
    }

    public function testItAllowsLoginWithSecondaryPassword()
    {
        $user = factory(Account::class)->create();
        $user->setPassword('my-secure-password');
        $this->assertTrue($user->hasPassword());

        Auth::guard('vatsim-sso')->login($user);
        $response = SecondaryLoginController::attemptSecondaryAuth($user);

        $this->assertTrue($response->isRedirect());
        $this->assertEquals(route('auth-secondary'), $response->getTargetUrl());
    }

    public function testItRedirectsToIntendedUrl()
    {
        $intendedUrl = 'https://www.vatsim.net';
        $user = factory(Account::class)->create();

        Session::put('url.intended', $intendedUrl);
        Auth::guard('vatsim-sso')->login($user);
        $response = SecondaryLoginController::attemptSecondaryAuth($user);

        $this->assertTrue($response->isRedirect());
        $this->assertNull(Session::get('url.intended'));
        $this->assertEquals($intendedUrl, $response->getTargetUrl());
    }

    public function testItLogsAUserOut()
    {
        $user = factory(Account::class)->create();

        $this->actingAs($user);
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))
            ->assertRedirect(route('site.home'));
        $this->assertGuest();
    }

    public function testItLogsAUserOutOfTheVatsimSSOGuard()
    {
        $user = factory(Account::class)->create();

        $this->actingAs($user, 'vatsim-sso');
        $this->assertAuthenticatedAs($user, 'vatsim-sso');

        $this->post(route('logout'))
            ->assertRedirect(route('site.home'));
        $this->assertGuest();
    }
}
