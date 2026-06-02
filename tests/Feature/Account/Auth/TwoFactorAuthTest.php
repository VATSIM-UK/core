<?php

namespace Tests\Feature\Account\Auth;

use App\Models\Mship\Account;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Fortify;
use PHPUnit\Framework\Attributes\Test;
use PragmaRX\Google2FA\Google2FA;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TwoFactorAuthTest extends TestCase
{
    protected Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create([
            'password' => 'secret-password',
        ]);
    }

    #[Test]
    public function mandatory_two_factor_middleware_redirects_to_setup(): void
    {
        $role = factory(Role::class)->create(['two_factor_mandatory' => true]);
        $this->account->assignRole($role);

        $this->actingAs($this->account)
            ->get(route('mship.manage.dashboard'))
            ->assertRedirect(route('two-factor.setup'));
    }

    #[Test]
    public function setup_page_is_accessible_when_two_factor_is_mandatory(): void
    {
        $role = factory(Role::class)->create(['two_factor_mandatory' => true]);
        $this->account->assignRole($role);

        $this->actingAs($this->account)
            ->get(route('two-factor.setup'))
            ->assertOk()
            ->assertSee('Two-Factor Authentication Setup');
    }

    #[Test]
    public function secondary_login_redirects_to_two_factor_challenge_when_enabled(): void
    {
        $this->enableTwoFactorFor($this->account);

        $this->actingAs($this->account, 'vatsim-sso')
            ->post(route('auth-secondary.post'), [
                'password' => 'secret-password',
            ])
            ->assertRedirect(route('two-factor.login'));

        $this->assertGuest('web');
        $this->assertEquals($this->account->id, session('login.id'));
    }

    #[Test]
    public function logout_clears_partial_two_factor_session(): void
    {
        session([
            'login.id' => $this->account->id,
            'login.remember' => false,
            'auth.password_confirmed_at' => now()->unix(),
        ]);

        $this->actingAs($this->account)
            ->post(route('logout'))
            ->assertRedirect(route('site.home'));

        $this->assertGuest();
        $this->assertNull(session('login.id'));
        $this->assertNull(session('auth.password_confirmed_at'));
    }

    #[Test]
    public function challenge_page_hides_recovery_input_by_default(): void
    {
        $this->enableTwoFactorFor($this->account);

        session(['login.id' => $this->account->id]);

        $this->get(route('two-factor.login'))
            ->assertOk()
            ->assertSee('authenticator application', false)
            ->assertSee('Use a recovery code', false)
            ->assertSee('autocomplete="one-time-code"', false)
            ->assertSee('autocomplete="username"', false)
            ->assertSee('scheduleSubmitCheck()', false)
            ->assertSee('submitting', false)
            ->assertSee('x-if="useRecovery"', false);
    }

    #[Test]
    public function challenge_page_can_switch_to_recovery_mode(): void
    {
        $this->enableTwoFactorFor($this->account);

        session(['login.id' => $this->account->id]);

        $this->get(route('two-factor.login'))
            ->assertOk()
            ->assertSee('name="recovery_code"', false)
            ->assertSee('showRecovery()', false);
    }

    #[Test]
    public function challenge_requires_partial_login_session(): void
    {
        $this->get(route('two-factor.login'))
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function invalid_two_factor_code_returns_to_challenge(): void
    {
        $this->enableTwoFactorFor($this->account);

        session(['login.id' => $this->account->id]);

        $this->from(route('two-factor.login'))
            ->post(route('two-factor.login.store'), ['code' => '000000'])
            ->assertRedirect(route('two-factor.login'))
            ->assertSessionHasErrors('code');

        $this->assertGuest('web');
    }

    #[Test]
    public function recovery_code_completes_login(): void
    {
        $this->enableTwoFactorFor($this->account);

        $recoveryCode = $this->account->fresh()->recoveryCodes()[0];

        session([
            'login.id' => $this->account->id,
            'login.remember' => false,
        ]);

        $this->post(route('two-factor.login.store'), [
            'recovery_code' => $recoveryCode,
        ])
            ->assertRedirect(route('mship.manage.dashboard'));

        $this->assertAuthenticatedAs($this->account);
        $this->assertNull(session('login.id'));
    }

    #[Test]
    public function secondary_login_sets_password_confirmed_at_before_two_factor_challenge(): void
    {
        $this->enableTwoFactorFor($this->account);

        $this->actingAs($this->account, 'vatsim-sso')
            ->post(route('auth-secondary.post'), [
                'password' => 'secret-password',
            ])
            ->assertRedirect(route('two-factor.login'));

        $this->assertNotNull(session('auth.password_confirmed_at'));
    }

    #[Test]
    public function setup_page_displays_manual_secret_when_pending_confirmation(): void
    {
        app(EnableTwoFactorAuthentication::class)($this->account, true);

        $secret = Fortify::currentEncrypter()->decrypt($this->account->fresh()->two_factor_secret);

        $this->actingAs($this->account)
            ->get(route('two-factor.setup'))
            ->assertOk()
            ->assertSee($secret, false)
            ->assertSee('Copy to clipboard', false);
    }

    #[Test]
    public function invalid_setup_confirmation_code_returns_to_setup_with_error(): void
    {
        app(EnableTwoFactorAuthentication::class)($this->account, true);

        $this->actingAs($this->account)
            ->withSession(['auth.password_confirmed_at' => now()->unix()])
            ->from(route('two-factor.setup'))
            ->post(route('two-factor.confirm'), ['code' => '000000'])
            ->assertRedirect(route('two-factor.setup'))
            ->assertSessionHasErrors('code', null, 'confirmTwoFactorAuthentication');

        $this->assertNull($this->account->fresh()->two_factor_confirmed_at);

        $this->actingAs($this->account)
            ->get(route('two-factor.setup'))
            ->assertOk()
            ->assertSee('The provided two factor authentication code was invalid.', false);
    }

    #[Test]
    public function confirm_password_redirect_accepts_same_host_urls(): void
    {
        $redirect = route('site.home');

        $this->actingAs($this->account)
            ->post(route('two-factor.confirm-password.store'), [
                'password' => 'secret-password',
                'redirect' => $redirect,
            ])
            ->assertRedirect($redirect);
    }

    #[Test]
    public function confirm_password_redirect_rejects_external_urls(): void
    {
        $this->actingAs($this->account)
            ->post(route('two-factor.confirm-password.store'), [
                'password' => 'secret-password',
                'redirect' => 'https://example.com/phishing',
            ])
            ->assertRedirect(route('mship.manage.dashboard'));
    }

    #[Test]
    public function confirming_two_factor_redirects_to_backup_codes_page(): void
    {
        app(EnableTwoFactorAuthentication::class)($this->account, true);

        $secret = Fortify::currentEncrypter()->decrypt($this->account->fresh()->two_factor_secret);
        $code = app(Google2FA::class)->getCurrentOtp($secret);
        $recoveryCode = $this->account->fresh()->recoveryCodes()[0];

        $this->actingAs($this->account)
            ->withSession(['auth.password_confirmed_at' => now()->unix()])
            ->post(route('two-factor.confirm'), ['code' => $code])
            ->assertRedirect(route('two-factor.backup-codes'))
            ->assertSessionHas('success');

        $this->actingAs($this->account)
            ->get(route('two-factor.backup-codes'))
            ->assertOk()
            ->assertSee('Save Your Recovery Codes', false)
            ->assertSee('Why are recovery codes important?', false)
            ->assertSee($recoveryCode, false)
            ->assertSee('Copy to clipboard', false)
            ->assertSee('Download as text file', false)
            ->assertSee('Continue to dashboard', false);
    }

    #[Test]
    public function backup_codes_page_requires_enabled_two_factor(): void
    {
        $this->actingAs($this->account)
            ->get(route('two-factor.backup-codes'))
            ->assertRedirect(route('two-factor.setup'));
    }

    #[Test]
    public function setup_page_shows_manage_view_when_two_factor_is_enabled(): void
    {
        $this->enableTwoFactorFor($this->account);

        $this->actingAs($this->account)
            ->get(route('two-factor.setup'))
            ->assertOk()
            ->assertSee('Recovery Codes', false)
            ->assertSee('Why are recovery codes important?', false)
            ->assertSee('Copy to clipboard', false)
            ->assertSee('Download as text file', false);
    }

    #[Test]
    public function two_factor_challenge_completes_login(): void
    {
        $this->enableTwoFactorFor($this->account);

        $secret = Fortify::currentEncrypter()->decrypt($this->account->fresh()->two_factor_secret);
        $code = app(Google2FA::class)->getCurrentOtp($secret);

        session([
            'login.id' => $this->account->id,
            'login.remember' => false,
        ]);

        $this->post(route('two-factor.login.store'), [
            'code' => $code,
        ])
            ->assertRedirect(route('mship.manage.dashboard'));

        $this->assertAuthenticatedAs($this->account);
        $this->assertNull(session('login.id'));
    }

    protected function enableTwoFactorFor(Account $account): void
    {
        app(EnableTwoFactorAuthentication::class)($account, true);

        $account->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();
    }
}
