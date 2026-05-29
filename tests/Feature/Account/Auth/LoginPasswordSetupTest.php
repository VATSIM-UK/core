<?php

namespace Tests\Feature\Account\Auth;

use App\Auth\LoginFlow;
use App\Models\Mship\Account;
use Illuminate\Http\Request;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoginPasswordSetupTest extends TestCase
{
    protected Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create([
            'password' => null,
        ]);
    }

    #[Test]
    public function vatsim_oauth_redirects_to_login_password_setup_when_mandatory_and_missing(): void
    {
        $role = factory(Role::class)->create(['password_mandatory' => true]);
        $this->account->assignRole($role);
        $this->enableTwoFactorFor($this->account);

        $response = LoginFlow::redirectAfterVatsimOAuth(
            Request::create('/'),
            $this->account->fresh(),
        );

        $this->assertTrue($response->isRedirect(route('login.password.setup')));
    }

    #[Test]
    public function login_password_setup_page_requires_vatsim_sso_authentication(): void
    {
        $role = factory(Role::class)->create(['password_mandatory' => true]);
        $this->account->assignRole($role);

        $this->get(route('login.password.setup'))
            ->assertRedirect(route('landing'));
    }

    #[Test]
    public function login_password_setup_is_accessible_during_partial_login(): void
    {
        $role = factory(Role::class)->create(['password_mandatory' => true]);
        $this->account->assignRole($role);

        $this->actingAs($this->account, 'vatsim-sso')
            ->get(route('login.password.setup'))
            ->assertOk()
            ->assertSee('Secondary Password Required');
    }

    #[Test]
    public function completing_login_password_setup_redirects_to_two_factor_challenge(): void
    {
        $role = factory(Role::class)->create(['password_mandatory' => true]);
        $this->account->assignRole($role);
        $this->enableTwoFactorFor($this->account);

        $this->actingAs($this->account, 'vatsim-sso')
            ->post(route('login.password.setup.store'), [
                'new_password' => 'Secret123',
                'new_password_confirmation' => 'Secret123',
            ])
            ->assertRedirect(route('two-factor.login'));

        $this->assertGuest('web');
        $this->assertEquals($this->account->id, session('login.id'));
        $this->assertTrue($this->account->fresh()->verifyPassword('Secret123'));
    }

    #[Test]
    public function completing_login_password_setup_without_two_factor_establishes_web_session(): void
    {
        $role = factory(Role::class)->create(['password_mandatory' => true]);
        $this->account->assignRole($role);

        $this->actingAs($this->account, 'vatsim-sso')
            ->post(route('login.password.setup.store'), [
                'new_password' => 'Secret123',
                'new_password_confirmation' => 'Secret123',
            ])
            ->assertRedirect(route('site.home'));

        $this->assertAuthenticatedAs($this->account);
    }

    #[Test]
    public function login_password_setup_redirects_away_when_password_is_not_mandatory(): void
    {
        $this->actingAs($this->account, 'vatsim-sso')
            ->get(route('login.password.setup'))
            ->assertRedirect(route('site.home'));
    }

    #[Test]
    public function login_password_setup_sets_password_confirmed_at_before_two_factor_challenge(): void
    {
        $role = factory(Role::class)->create(['password_mandatory' => true]);
        $this->account->assignRole($role);
        $this->enableTwoFactorFor($this->account);

        $this->actingAs($this->account, 'vatsim-sso')
            ->post(route('login.password.setup.store'), [
                'new_password' => 'Secret123',
                'new_password_confirmation' => 'Secret123',
            ])
            ->assertRedirect(route('two-factor.login'));

        $this->assertNotNull(session('auth.password_confirmed_at'));
    }

    #[Test]
    public function vatsim_oauth_skips_password_setup_when_not_mandatory_and_goes_to_two_factor(): void
    {
        $this->enableTwoFactorFor($this->account);

        $request = Request::create('/');
        $request->setLaravelSession($this->app['session.store']);

        $response = LoginFlow::establishWebSession(
            $request,
            $this->account->fresh(),
            remember: true,
        );

        $this->assertTrue($response->isRedirect(route('two-factor.login')));
        $this->assertGuest('web');
        $this->assertEquals($this->account->id, session('login.id'));
    }

    #[Test]
    public function vatsim_oauth_still_redirects_to_secondary_login_when_password_exists(): void
    {
        $this->account->forceFill(['password' => 'Secret123'])->save();
        $this->enableTwoFactorFor($this->account);

        $response = LoginFlow::redirectAfterVatsimOAuth(
            Request::create('/'),
            $this->account->fresh(),
        );

        $this->assertTrue($response->isRedirect(route('auth-secondary')));
    }

    protected function enableTwoFactorFor(Account $account): void
    {
        app(EnableTwoFactorAuthentication::class)($account, true);

        $account->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();
    }
}
