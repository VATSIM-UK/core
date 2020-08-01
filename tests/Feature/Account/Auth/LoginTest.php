<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\SecondaryLoginController;
use App\Models\Mship\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LoginTest extends TestCase
{
    protected $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->account = factory(Account::class)->create();
    }

    public function testItDirectsToVatsimSSO()
    {
        config()->set('vatsim-connect.base', 'https://my-oauth-url.com');
        config()->set('vatsim-connect.id', 12345);
        config()->set('vatsim-connect.scopes', explode(',', 'my-first,my-second,my-third'));

        $this->assertGuest();

        $this->get(route('login'))
            ->assertRedirect();
        $this->post(route('login'))
            ->assertRedirect();

        $redirectUrl = $this->get(route('login'))
            ->headers->get('location');

        $this->assertStringContainsString(config('vatsim-connect.base'), $redirectUrl);
        $this->assertStringContainsString('state', $redirectUrl);
        $this->assertStringContainsString('scope=' . implode('%20', config('vatsim-connect.scopes')), $redirectUrl);
        $this->assertStringContainsString('response_type=code', $redirectUrl);
        $this->assertStringContainsString('redirect_uri=' . urlencode(route('login.post')), $redirectUrl);
        $this->assertStringContainsString('client_id=' . config('vatsim-connect.id'), $redirectUrl);
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
        $this->assertFalse($this->account->hasPassword());

        Auth::guard('vatsim-sso')->login($this->account);
        $response = SecondaryLoginController::attemptSecondaryAuth($this->account);

        $this->assertTrue($response->isRedirect());
        $this->assertEquals(route('site.home'), $response->getTargetUrl());
    }

    public function testItAllowsLoginWithSecondaryPassword()
    {
        $this->account->setPassword('my-secure-password');
        $this->assertTrue($this->account->hasPassword());

        Auth::guard('vatsim-sso')->login($this->account);
        $response = SecondaryLoginController::attemptSecondaryAuth($this->account);

        $this->assertTrue($response->isRedirect());
        $this->assertEquals(route('auth-secondary'), $response->getTargetUrl());
    }

    public function testItRedirectsToIntendedUrl()
    {
        $intendedUrl = 'https://www.vatsim.net';
        $this->account = factory(Account::class)->create();

        Session::put('url.intended', $intendedUrl);
        Auth::guard('vatsim-sso')->login($this->account);
        $response = SecondaryLoginController::attemptSecondaryAuth($this->account);

        $this->assertTrue($response->isRedirect());
        $this->assertNull(Session::get('url.intended'));
        $this->assertEquals($intendedUrl, $response->getTargetUrl());
    }

    public function testItLogsAUserOut()
    {
        $this->actingAs($this->account);
        $this->assertAuthenticatedAs($this->account);

        $this->post(route('logout'))
            ->assertRedirect(route('site.home'));
        $this->assertGuest();
    }

    public function testItLogsAUserOutOfTheVatsimSSOGuard()
    {
        $this->actingAs($this->account, 'vatsim-sso');
        $this->assertAuthenticatedAs($this->account, 'vatsim-sso');

        $this->post(route('logout'))
            ->assertRedirect(route('site.home'));
        $this->assertGuest();
    }
}
