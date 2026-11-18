<?php

namespace Tests\Feature\API;

use Illuminate\Support\Facades\Route;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PassportRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Passport::loadKeysFrom($this->passportKeysPath());
    }

    #[Test]
    public function members_cannot_create_oauth_clients()
    {
        $this->actingAs($this->user)
            ->postJson('/oauth/clients', [
                'name' => 'VATSIM UK Helpdesk',
                'redirect' => 'https://evil.example/callback',
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('oauth_clients', ['name' => 'VATSIM UK Helpdesk']);
    }

    #[Test]
    public function members_cannot_list_oauth_clients()
    {
        $this->actingAs($this->user)
            ->getJson('/oauth/clients')
            ->assertNotFound();
    }

    #[Test]
    public function members_cannot_manage_personal_access_tokens()
    {
        $this->actingAs($this->user)
            ->postJson('/oauth/personal-access-tokens', ['name' => 'Token', 'scopes' => []])
            ->assertNotFound();

        $this->actingAs($this->user)
            ->getJson('/oauth/personal-access-tokens')
            ->assertNotFound();
    }

    #[Test]
    public function members_cannot_list_authorized_access_tokens()
    {
        $this->actingAs($this->user)
            ->getJson('/oauth/tokens')
            ->assertNotFound();
    }

    #[Test]
    public function guests_cannot_reach_client_management_endpoints()
    {
        $this->getJson('/oauth/clients')->assertNotFound();
        $this->postJson('/oauth/clients', ['name' => 'Rogue'])->assertNotFound();
    }

    #[Test]
    public function the_authorization_endpoint_is_registered()
    {
        $this->assertTrue(Route::has('passport.authorizations.authorize'));
        $this->assertTrue(Route::has('passport.authorizations.approve'));
        $this->assertTrue(Route::has('passport.authorizations.deny'));
    }

    #[Test]
    public function the_token_endpoints_are_registered()
    {
        $this->assertTrue(Route::has('passport.token'));
        $this->assertTrue(Route::has('passport.token.refresh'));

        $this->postJson('/oauth/token', [])
            ->assertStatus(400)
            ->assertJson(['error' => 'unsupported_grant_type']);
    }

    #[Test]
    public function members_can_still_authorize_a_client()
    {
        $client = Client::factory()->create([
            'name' => 'Moodle',
            'redirect' => 'https://moodle.example/oauth/callback',
        ]);

        $this->actingAs($this->user)
            ->get('/oauth/authorize?'.http_build_query([
                'client_id' => $client->id,
                'redirect_uri' => $client->redirect,
                'response_type' => 'code',
                'scope' => '',
                'state' => 'random-state',
            ]))
            ->assertSuccessful()
            ->assertSee('Moodle');
    }

    #[Test]
    public function guests_are_sent_to_login_when_authorizing()
    {
        $client = Client::factory()->create([
            'redirect' => 'https://moodle.example/oauth/callback',
        ]);

        $response = $this->get('/oauth/authorize?'.http_build_query([
            'client_id' => $client->id,
            'redirect_uri' => $client->redirect,
            'response_type' => 'code',
            'scope' => '',
        ]));

        $this->assertNotSame(404, $response->getStatusCode());
        $this->assertStringContainsString('login', (string) $response->headers->get('Location'));
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
