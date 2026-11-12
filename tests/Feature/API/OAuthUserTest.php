<?php

namespace Tests\Feature\API;

use App\Models\Api\Request as ApiRequest;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Ban;
use App\Models\Mship\State;
use Carbon\Carbon;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OAuthUserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Passport::loadKeysFrom($this->passportKeysPath());
    }

    #[Test]
    public function it_returns_no_ban_info_for_a_member_who_is_not_banned(): void
    {
        $this->apiAccount();

        $response = $this->getJson('/api/user');

        $response->assertOk();
        $response->assertJsonPath('data.is_banned', false);
        $response->assertJsonPath('data.ban_info', null);
        $this->assertNull(ApiRequest::first()->response_full);
    }

    #[Test]
    public function it_returns_only_a_member_safe_summary_of_the_active_ban(): void
    {
        $account = $this->apiAccount();

        Ban::factory()->create([
            'account_id' => $account->id,
            'reason_extra' => 'Internal staff notes',
        ]);

        $response = $this->getJson('/api/user');

        $response->assertOk();
        $response->assertJsonPath('data.is_banned', true);
        $response->assertJsonPath('data.ban_info.is_permanent', false);
        $this->assertSame(
            ['type', 'period_start', 'period_finish', 'is_permanent'],
            array_keys($response->json('data.ban_info')),
        );
        $response->assertJsonMissingPath('data.ban_info.banned_by');
        $response->assertJsonMissingPath('data.ban_info.reason_extra');
    }

    #[Test]
    public function it_ignores_a_ban_that_has_already_expired(): void
    {
        $account = $this->apiAccount();

        Ban::factory()->create([
            'account_id' => $account->id,
            'period_start' => Carbon::now()->subDays(2),
            'period_finish' => Carbon::now()->subDay(),
        ]);

        $response = $this->getJson('/api/user');

        $response->assertOk();
        $response->assertJsonPath('data.is_banned', false);
        $response->assertJsonPath('data.ban_info', null);
    }

    private function apiAccount(): Account
    {
        $account = Account::factory()->createQuietly();
        $account->addState(State::findByCode('DIVISION'));

        Passport::actingAs($account, [], 'api');
        $account->oAuthToken()->setRelation('client', Client::factory()->create());

        return $account;
    }

    private function passportKeysPath(): string
    {
        $path = storage_path('framework/testing/passport');

        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }

        if (! file_exists($path.'/oauth-private.key')) {
            $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);

            openssl_pkey_export($key, $privateKey);

            file_put_contents($path.'/oauth-private.key', $privateKey);
            file_put_contents($path.'/oauth-public.key', openssl_pkey_get_details($key)['key']);
        }

        return $path;
    }
}
