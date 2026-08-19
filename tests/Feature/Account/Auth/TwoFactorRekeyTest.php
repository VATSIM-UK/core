<?php

namespace Tests\Feature\Account\Auth;

use App\Models\Mship\Account;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Fortify;
use PHPUnit\Framework\Attributes\Test;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorRekeyTest extends TestCase
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
    public function rekey_requires_authentication(): void
    {
        $this->post(route('two-factor.rekey'))
            ->assertRedirect();
    }

    #[Test]
    public function rekey_requires_password_confirmation(): void
    {
        $this->enableTwoFactorFor($this->account);

        $this->actingAs($this->account)
            ->post(route('two-factor.rekey'))
            ->assertRedirect(route('two-factor.confirm-password', [
                'redirect' => route('two-factor.rekey'),
            ]));
    }

    #[Test]
    public function rekey_replaces_the_secret_and_clears_confirmation(): void
    {
        $this->enableTwoFactorFor($this->account);

        $originalSecret = $this->account->fresh()->two_factor_secret;
        $originalCodes = $this->account->fresh()->two_factor_recovery_codes;

        $this->actingAs($this->account)
            ->withSession(['auth.password_confirmed_at' => now()->unix()])
            ->post(route('two-factor.rekey'))
            ->assertRedirect(route('two-factor.setup'));

        $fresh = $this->account->fresh();

        $this->assertNotEquals($originalSecret, $fresh->two_factor_secret);
        $this->assertNotEquals($originalCodes, $fresh->two_factor_recovery_codes);
        $this->assertNull($fresh->two_factor_confirmed_at);
        $this->assertFalse($fresh->hasEnabledTwoFactorAuthentication());
    }

    #[Test]
    public function rekey_then_confirming_the_new_device_re_enables_two_factor(): void
    {
        $this->enableTwoFactorFor($this->account);

        $this->actingAs($this->account)
            ->withSession(['auth.password_confirmed_at' => now()->unix()])
            ->post(route('two-factor.rekey'));

        $newSecret = Fortify::currentEncrypter()->decrypt($this->account->fresh()->two_factor_secret);
        $code = app(Google2FA::class)->getCurrentOtp($newSecret);

        $this->actingAs($this->account)
            ->withSession(['auth.password_confirmed_at' => now()->unix()])
            ->post(route('two-factor.confirm'), ['code' => $code])
            ->assertRedirect(route('two-factor.backup-codes'));

        $this->assertTrue($this->account->fresh()->hasEnabledTwoFactorAuthentication());
    }

    #[Test]
    public function manage_page_offers_replace_authenticator(): void
    {
        $this->enableTwoFactorFor($this->account);

        $this->actingAs($this->account)
            ->get(route('two-factor.setup'))
            ->assertOk()
            ->assertSee('Replace Authenticator App', false)
            ->assertSee(route('two-factor.rekey'), false);
    }

    #[Test]
    public function manage_page_offers_replace_authenticator_to_mandatory_users(): void
    {
        $role = factory(\Spatie\Permission\Models\Role::class)->create(['two_factor_mandatory' => true]);
        $this->account->assignRole($role);
        $this->enableTwoFactorFor($this->account);

        $this->actingAs($this->account)
            ->get(route('two-factor.setup'))
            ->assertOk()
            ->assertSee('Replace Authenticator App', false)
            ->assertDontSee('Disable Two-Factor Authentication', false);
    }

    protected function enableTwoFactorFor(Account $account): void
    {
        app(EnableTwoFactorAuthentication::class)($account, true);

        $account->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();
    }
}
