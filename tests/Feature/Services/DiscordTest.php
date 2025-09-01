<?php

namespace Tests\Feature\Services;

use App\Models\Mship\Account;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use League\OAuth2\Client\Token\AccessToken;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;

class DiscordTest extends TestCase
{
    #[Test]
    public function test_it_shows_registration_page()
    {
        $this->actingAs($this->user)
            ->get(route('discord.show'))
            ->assertSee('Discord Registration')
            ->assertOk();
    }

    #[Test]
    public function test_it_redirects_to_o_auth()
    {
        $response = $this->actingAs($this->user)
            ->get(route('discord.create'))
            ->assertRedirect();

        $expectedUrl = 'https://discord.com/oauth2/authorize';
        $redirectUrl = explode('?', $response->getTargetUrl())[0];

        $this->assertEquals($expectedUrl, $redirectUrl);
    }

    #[Test]
    public function test_it_passes_paramaters_to_o_auth()
    {
        Config::set('services.discord.redirect_uri', 'https://example.com/store');
        Config::set('services.discord.client_id', 123456789);

        $response = $this->actingAs($this->user)
            ->get(route('discord.create'));

        $redirectUrl = explode('?', $response->getTargetUrl());
        $queryString = collect(explode('&', $redirectUrl[1]));

        $parameters = $queryString->mapWithKeys(function ($item) {
            return [explode('=', $item)[0] => explode('=', $item)[1]];
        });

        $this->assertArrayHasKey('scope', $parameters);
        $this->assertArrayHasKey('state', $parameters);
        $this->assertArrayHasKey('response_type', $parameters);
        $this->assertArrayHasKey('approval_prompt', $parameters);
        $this->assertArrayHasKey('redirect_uri', $parameters);
        $this->assertArrayHasKey('client_id', $parameters);

        $expected = [
            'scope' => 'identify%20guilds.join',
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'redirect_uri' => urlencode(config('services.discord.redirect_uri')),
            'client_id' => (int) config('services.discord.client_id'),
        ];

        $this->assertEquals($parameters->except('state')->toArray(), $expected);
    }

    #[Test]
    public function test_it_redirects_when_code_missing()
    {
        $emptyString = $this->actingAs($this->user)
            ->from(route('discord.show'))
            ->get(route('discord.store', ['code' => '']));

        $missingCode = $this->actingAs($this->user)
            ->from(route('discord.show'))
            ->get(route('discord.store'));

        $nullCode = $this->actingAs($this->user)
            ->from(route('discord.show'))
            ->get(route('discord.store', ['code' => null]));

        $emptyString->assertRedirect(route('discord.show'))->assertSessionHasErrors('code');
        $missingCode->assertRedirect(route('discord.show'))->assertSessionHasErrors('code');
        $nullCode->assertRedirect(route('discord.show'))->assertSessionHasErrors('code');
    }

    #[Test]
    public function test_it_reports_when_user_in_too_many_servers()
    {
        $this->instance(\Wohali\OAuth2\Client\Provider\Discord::class, Mockery::mock(\Wohali\OAuth2\Client\Provider\Discord::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAccessToken')->andReturn(new AccessToken(['access_token' => '123456', 'scope' => 'identify guilds.join']));
            $mock->shouldReceive('getResourceOwner')->andReturn(new DiscordResourceOwner([
                'id' => '123456789',
            ]));
        }));

        Http::fake([
            'discord.com/api/v6/guilds//members/123456789' => Http::response(['message' => 'You are at the 100 server limit.', 'code' => 30001], 304),
        ]);

        $this->actingAs($this->user)
            ->get(route('discord.store', [
                'code' => '123456789',
            ]))
            ->assertRedirect(route('discord.show'))
            ->assertSessionHas('error', 'You have reached your Discord server limit! You must leave a server before you can join another one');
    }

    #[Test]
    public function test_rate_limit_event_is_dispatched_and_retry_succeeds()
    {
        /** @var Account $account */
        $account = Account::factory()->create([
            'discord_id' => 9876543,
            'discord_access_token' => 'xyz',
        ]);

        Event::fake();

        // 1st call returns retry_after -> rate limit
        // 2nd call succeeds
        Http::fakeSequence()
            ->push(['retry_after' => 1], 429)
            ->push([], 200);

        $discord = new \App\Libraries\Discord;
        $result = $discord->grantRoleById($account, 99);

        $this->assertTrue($result);
        Event::assertDispatched('discord.rate_limited');
        Event::assertDispatched('discord.api_succeeded');
    }
}
