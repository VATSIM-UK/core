<?php

namespace Tests\Feature\Account\Auth;

use App\Auth\LoginFlow;
use App\Models\Mship\Account;
use App\Providers\VATSIMOAuthProvider;
use Illuminate\Http\Request;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use Tests\TestCase;

class LoginTest extends TestCase
{
    protected $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->account = Account::factory()->create();
    }

    public function test_it_directs_to_vatsim_sso()
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

        $this->assertStringContainsString(config('services.vatsim-net.connect.base'), $redirectUrl);
        $this->assertStringContainsString('state', $redirectUrl);
        $this->assertStringContainsString('scope='.implode('%20', config('services.vatsim-net.connect.scopes')), $redirectUrl);
        $this->assertStringContainsString('response_type=code', $redirectUrl);
        $this->assertStringContainsString('redirect_uri='.urlencode(route('login.post')), $redirectUrl);
        $this->assertStringContainsString('client_id='.config('services.vatsim-net.connect.id'), $redirectUrl);
    }

    public function test_it_rejects_an_empty_state_when_no_state_is_stored_in_the_session()
    {
        $this->mock(VATSIMOAuthProvider::class, function ($mock) {
            $mock->shouldReceive('getAccessToken')->never();
        });

        $this->get(route('login').'?code=attacker-code&state=')
            ->assertRedirect(route('landing'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_it_rejects_a_state_that_does_not_match_the_session()
    {
        $this->mock(VATSIMOAuthProvider::class, function ($mock) {
            $mock->shouldReceive('getAccessToken')->never();
        });

        $this->withSession(['vatsimauthstate' => 'expected-state'])
            ->get(route('login').'?code=attacker-code&state=wrong-state')
            ->assertRedirect(route('landing'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_it_continues_the_login_when_the_state_matches_the_session()
    {
        $this->mock(VATSIMOAuthProvider::class, function ($mock) {
            $mock->shouldReceive('getAccessToken')->once()->andReturn(new AccessToken(['access_token' => 'token']));
            $mock->shouldReceive('getResourceOwner')->once()->andReturn(new GenericResourceOwner(['data' => null], 'id'));
        });

        $this->withSession(['vatsimauthstate' => 'expected-state'])
            ->get(route('login').'?code=valid-code&state=expected-state')
            ->assertRedirect(route('landing'))
            ->assertSessionHas('error', 'You cannot use our services unless you provide the relevant permissions upon login. Please try again.');

        $this->assertGuest();
    }

    public function test_it_rejects_a_resource_owner_with_an_invalid_token()
    {
        $this->mock(VATSIMOAuthProvider::class, function ($mock) {
            $mock->shouldReceive('getAccessToken')->once()->andReturn(new AccessToken(['access_token' => 'token']));
            $mock->shouldReceive('getResourceOwner')->once()->andReturn(new GenericResourceOwner(['data' => [
                'cid' => '1000001',
                'personal' => ['name_first' => 'Test', 'name_last' => 'User', 'email' => 'test@example.com'],
                'vatsim' => [
                    'rating' => ['id' => 1],
                    'pilotrating' => ['id' => 0],
                    'division' => ['id' => 1],
                    'region' => ['id' => 1],
                ],
                'oauth' => ['token_valid' => false],
            ]], 'id'));
        });

        $this->withSession(['vatsimauthstate' => 'expected-state'])
            ->get(route('login').'?code=valid-code&state=expected-state')
            ->assertRedirect(route('landing'))
            ->assertSessionHas('error', 'You cannot use our services unless you provide the relevant permissions upon login. Please try again.');

        $this->assertGuest();
        $this->assertDatabaseMissing('mship_account', ['id' => 1000001]);
    }

    public function test_vatsim_oauth_login_does_not_force_remember_me()
    {
        $request = Request::create('/');
        $request->setLaravelSession($this->app['session.store']);

        LoginFlow::redirectAfterVatsimOAuth($request, $this->account->fresh());

        $this->assertAuthenticatedAs($this->account, 'web');
        $this->assertNull($this->account->fresh()->remember_token, 'Login must not force remember-me.');
    }

    public function test_vatsim_oauth_login_redirects_to_two_factor_challenge_when_enabled()
    {
        app(EnableTwoFactorAuthentication::class)($this->account, true);
        $this->account->forceFill(['two_factor_confirmed_at' => now()])->saveQuietly();

        $request = Request::create('/');
        $request->setLaravelSession($this->app['session.store']);

        $response = LoginFlow::redirectAfterVatsimOAuth($request, $this->account->fresh());

        $this->assertSame(route('two-factor.login'), $response->headers->get('Location'));
        $this->assertSame($this->account->id, session('login.id'));
        $this->assertFalse(session('login.remember'));
        $this->assertSame(route('site.home'), session('url.intended'));
        $this->assertGuest('web');
    }

    public function test_it_logs_a_user_out()
    {
        $this->actingAs($this->account);
        $this->assertAuthenticatedAs($this->account);

        $this->post(route('logout'))
            ->assertRedirect(route('site.home'));
        $this->assertGuest();
    }

    public function test_it_invalidates_the_session_on_logout()
    {
        $this->actingAs($this->account);

        $sessionId = session()->getId();
        $csrfToken = session()->token();
        session(['session-marker' => 'value']);

        $this->post(route('logout'))
            ->assertRedirect(route('site.home'));

        $this->assertGuest();
        $this->assertNotSame($sessionId, session()->getId());
        $this->assertNotSame($csrfToken, session()->token());
        $this->assertNull(session('session-marker'));
    }

    public function test_it_logs_a_user_out_of_the_vatsim_sso_guard()
    {
        $this->actingAs($this->account, 'vatsim-sso');
        $this->assertAuthenticatedAs($this->account, 'vatsim-sso');

        $this->post(route('logout'))
            ->assertRedirect(route('site.home'));
        $this->assertGuest();
    }
}
