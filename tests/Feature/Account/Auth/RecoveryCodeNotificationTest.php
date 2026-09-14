<?php

namespace Tests\Feature\Account\Auth;

use App\Models\Mship\Account;
use App\Notifications\Mship\RecoveryCodeUsed;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Fortify;
use PHPUnit\Framework\Attributes\Test;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class RecoveryCodeNotificationTest extends TestCase
{
    protected Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create([
            'password' => 'secret-password',
        ]);

        app(EnableTwoFactorAuthentication::class)($this->account, true);
        $this->account->forceFill(['two_factor_confirmed_at' => now()])->save();
    }

    #[Test]
    public function using_a_recovery_code_notifies_the_member(): void
    {
        $recoveryCode = $this->account->fresh()->recoveryCodes()[0];

        session([
            'login.id' => $this->account->id,
            'login.remember' => false,
        ]);

        $this->post(route('two-factor.login.store'), ['recovery_code' => $recoveryCode])
            ->assertRedirect(route('two-factor.setup'));

        Notification::assertSentTo($this->account, RecoveryCodeUsed::class);
    }

    #[Test]
    public function using_an_authenticator_code_does_not_notify_the_member(): void
    {
        $secret = Fortify::currentEncrypter()->decrypt($this->account->fresh()->two_factor_secret);
        $code = app(Google2FA::class)->getCurrentOtp($secret);

        session([
            'login.id' => $this->account->id,
            'login.remember' => false,
        ]);

        $this->post(route('two-factor.login.store'), ['code' => $code])
            ->assertRedirect(route('mship.manage.dashboard'));

        Notification::assertNotSentTo($this->account, RecoveryCodeUsed::class);
    }
}
