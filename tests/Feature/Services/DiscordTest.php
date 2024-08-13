<?php

namespace Tests\Feature\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use League\OAuth2\Client\Token\AccessToken;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;

class DiscordTest extends TestCase
{
    /** @test */
    public function testItShowsRegistrationPage()
    {
        $this->actingAs($this->user)
            ->get(route('discord.show'))
            ->assertSee('Discord Registration')
            ->assertOk();
    }

    /** @test */
    public function testItRedirectsToOAuth()
    {
        $response = $this->actingAs($this->user)
            ->get(route('discord.create'))
            ->assertRedirect();

        $expectedUrl = 'https://discord.com/oauth2/authorize';
        $redirectUrl = explode('?', $response->getTargetUrl())[0];

        $this->assertEquals($expectedUrl, $redirectUrl);
    }

    /** @test */
    public function testItPassesParamatersToOAuth()
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

    /** @test */
    public function testItRedirectsWhenCodeMissing()
    {
        $emptyString = $this->actingAs($this->user)
            ->from(route('discord.show'))
            ->get(route('discord.store', [
                'code' => '',
            ]));

        $missingCode = $this->actingAs($this->user)
            ->from(route('discord.show'))
            ->get(route('discord.store', [
                //
            ]));

        $nullCode = $this->actingAs($this->user)
            ->from(route('discord.show'))
            ->get(route('discord.store', [
                'code' => null,
            ]));

        $emptyString->assertRedirect(route('discord.show'))
            ->assertSessionHasErrors('code');
        $missingCode->assertRedirect(route('discord.show'))
            ->assertSessionHasErrors('code');
        $nullCode->assertRedirect(route('discord.show'))
            ->assertSessionHasErrors('code');
    }

    /** @test */
    public function testItReportsWhenUserInTooManyServers()
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
}
