<?php

namespace Tests\Feature\Account\Auth;

use App\Models\Mship\Account;
use Illuminate\Auth\SessionGuard as BaseSessionGuard;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RememberMeCookieTest extends TestCase
{
    #[Test]
    public function it_ignores_a_remember_cookie_whose_token_no_longer_matches(): void
    {
        $account = Account::factory()->createQuietly();
        $account->forceFill(['remember_token' => 'current-token'])->saveQuietly();

        $recaller = $account->id.'|stale-token|'.$this->cookieHash('irrelevant');

        $this->withCookie($this->recallerName(), $recaller)
            ->get(route('site.join'))
            ->assertOk();

        $this->assertGuest();
    }

    #[Test]
    public function it_authenticates_a_passwordless_account_from_a_valid_remember_cookie(): void
    {
        $account = Account::factory()->createQuietly();
        $account->forceFill(['remember_token' => 'current-token', 'password' => null])->saveQuietly();

        $recaller = $account->id.'|current-token|'.$this->cookieHash('');

        $this->withCookie($this->recallerName(), $recaller)
            ->get(route('site.join'))
            ->assertOk();

        $this->assertAuthenticatedAs($account, 'web');
    }

    #[Test]
    public function it_authenticates_from_a_pre_12_69_remember_cookie(): void
    {
        $account = Account::factory()->createQuietly();
        $account->forceFill([
            'remember_token' => 'current-token',
            'password' => 'hashed-password',
        ])->saveQuietly();

        $recaller = $account->id.'|current-token|'.$account->password;

        $this->withCookie($this->recallerName(), $recaller)
            ->get(route('site.join'))
            ->assertOk();

        $this->assertAuthenticatedAs($account, 'web');
    }

    private function cookieHash(string $passwordHash): string
    {
        return hash_hmac('sha256', $passwordHash, config('app.key'));
    }

    private function recallerName(): string
    {
        return 'remember_web_'.sha1(BaseSessionGuard::class);
    }
}
