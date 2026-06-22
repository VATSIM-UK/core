<?php

namespace Tests\Feature\Services;

use App\Exceptions\Discord\DiscordUserNotFoundException;
use App\Exceptions\Discord\GenericDiscordException;
use App\Libraries\Discord;
use App\Models\Mship\Account;
use App\Services\Discord\HoneypotService;
use Illuminate\Support\Facades\Cache;
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
    // ─── OAuth / Registration ───────────────────────────────────────

    #[Test]
    public function test_it_shows_registration_page()
    {
        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard'))
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
            ->from(route('mship.manage.dashboard'))
            ->get(route('discord.store', ['code' => '']));

        $missingCode = $this->actingAs($this->user)
            ->from(route('mship.manage.dashboard'))
            ->get(route('discord.store'));

        $nullCode = $this->actingAs($this->user)
            ->from(route('mship.manage.dashboard'))
            ->get(route('discord.store', ['code' => null]));

        $emptyString->assertRedirect(route('mship.manage.dashboard'))->assertSessionHasErrors('code');
        $missingCode->assertRedirect(route('mship.manage.dashboard'))->assertSessionHasErrors('code');
        $nullCode->assertRedirect(route('mship.manage.dashboard'))->assertSessionHasErrors('code');
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
            'discord.com/api/v10/guilds/*/members/123456789' => Http::response(['message' => 'You are at the 100 server limit.', 'code' => 30001], 304),
        ]);

        $this->actingAs($this->user)
            ->get(route('discord.store', [
                'code' => '123456789',
            ]))
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('error', 'You have reached your Discord server limit! You must leave a server before you can join another one');
    }

    // ─── Discord Library API Methods ────────────────────────────────

    #[Test]
    public function test_grant_role_by_id_sends_put_request()
    {
        $account = Account::factory()->createQuietly(['discord_id' => 12345]);

        Http::fake([
            'discord.com/api/v10/guilds/*/members/12345/roles/99' => Http::response([], 204),
        ]);

        $discord = new Discord;
        $result = $discord->grantRoleById($account, 99);

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->method() === 'PUT'
                && str_contains($request->url(), '/roles/99');
        });
    }

    #[Test]
    public function test_remove_role_by_id_sends_delete_request()
    {
        $account = Account::factory()->createQuietly(['discord_id' => 12345]);

        Http::fake([
            'discord.com/api/v10/guilds/*/members/12345/roles/99' => Http::response([], 204),
        ]);

        $discord = new Discord;
        $result = $discord->removeRoleById($account, 99);

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->method() === 'DELETE'
                && str_contains($request->url(), '/roles/99');
        });
    }

    #[Test]
    public function test_set_nickname_sends_patch_request_with_nick()
    {
        $account = Account::factory()->createQuietly(['discord_id' => 12345]);
        $nickname = 'Test User - 12345';

        Http::fake([
            'discord.com/api/v10/guilds/*/members/12345' => Http::response([], 204),
        ]);

        $discord = new Discord;
        $result = $discord->setNickname($account, $nickname);

        $this->assertTrue($result);

        Http::assertSent(function ($request) use ($nickname) {
            return $request->method() === 'PATCH'
                && $request->data() === ['nick' => $nickname];
        });
    }

    #[Test]
    public function test_set_roles_sends_patch_request_with_role_ids()
    {
        $account = Account::factory()->createQuietly(['discord_id' => 12345]);
        $roleIds = [1, 2, 3];

        Http::fake([
            'discord.com/api/v10/guilds/*/members/12345' => Http::response([], 204),
        ]);

        $discord = new Discord;
        $result = $discord->setRoles($account, $roleIds);

        $this->assertTrue($result);

        Http::assertSent(function ($request) use ($roleIds) {
            return $request->method() === 'PATCH'
                && $request->data() === ['roles' => $roleIds];
        });
    }

    #[Test]
    public function test_get_user_roles_returns_collection_of_role_ids()
    {
        $account = Account::factory()->createQuietly(['discord_id' => 12345]);

        Http::fake([
            'discord.com/api/v10/guilds/*/members/12345' => Http::response(['roles' => ['111', '222', '333']], 200),
        ]);

        $discord = new Discord;
        $roles = $discord->getUserRoles($account);

        $this->assertEquals(collect(['111', '222', '333']), $roles);
    }

    #[Test]
    public function test_get_user_roles_returns_empty_on_api_failure()
    {
        $account = Account::factory()->createQuietly(['discord_id' => 12345]);

        Http::fake([
            'discord.com/api/v10/guilds/*/members/12345' => Http::response([], 500),
        ]);

        $discord = new Discord;
        $roles = $discord->getUserRoles($account);

        $this->assertTrue($roles->isEmpty());
    }

    #[Test]
    public function test_kick_returns_true_when_user_not_found()
    {
        $account = Account::factory()->createQuietly(['discord_id' => 99999]);

        Http::fake([
            'discord.com/api/v10/guilds/*/members/99999' => Http::response(['message' => 'Unknown Member'], 404),
        ]);

        $discord = new Discord;
        $result = $discord->kick($account);

        $this->assertTrue($result);
    }

    // ─── Error Handling ─────────────────────────────────────────────

    #[Test]
    public function test_throws_discord_user_not_found_on_404()
    {
        $this->expectException(DiscordUserNotFoundException::class);

        $account = Account::factory()->createQuietly(['discord_id' => 99999]);

        Http::fake([
            'discord.com/api/v10/guilds/*/members/99999/roles/99' => Http::response(['message' => 'Unknown Member'], 404),
        ]);

        $discord = new Discord;
        $discord->grantRoleById($account, 99);
    }

    #[Test]
    public function test_throws_generic_exception_on_api_error()
    {
        $this->expectException(GenericDiscordException::class);

        $account = Account::factory()->createQuietly(['discord_id' => 12345]);

        Http::fake([
            'discord.com/api/v10/guilds/*/members/12345/roles/99' => Http::response(['message' => 'Bad Request'], 400),
        ]);

        $discord = new Discord;
        $discord->grantRoleById($account, 99);
    }

    // ─── Rate Limiting ──────────────────────────────────────────────

    #[Test]
    public function test_rate_limited_request_retries_on_429_then_succeeds()
    {
        Event::fake();

        $account = Account::factory()->createQuietly(['discord_id' => 12345]);

        Http::fakeSequence()
            ->push(['retry_after' => 0.001], 429)
            ->push([], 204);

        $discord = new Discord;
        $result = $discord->grantRoleById($account, 99);

        $this->assertTrue($result);
        Event::assertDispatched('discord.rate_limited');
        Event::assertDispatched('discord.api_succeeded');
    }

    #[Test]
    public function test_rate_limited_request_fires_events_on_each_attempt()
    {
        Event::fake();

        $account = Account::factory()->createQuietly(['discord_id' => 12345]);

        // Two rate-limits then success — tests exponential backoff path too
        Http::fakeSequence()
            ->push(['retry_after' => 0.001], 429)
            ->push(['retry_after' => 0.001], 429)
            ->push([], 204);

        $discord = new Discord;
        $result = $discord->grantRoleById($account, 99);

        $this->assertTrue($result);
        Event::assertDispatchedTimes('discord.rate_limited', 2);
        Event::assertDispatched('discord.api_succeeded');
    }

    #[Test]
    public function test_rate_limited_request_fails_after_exhausting_retries()
    {
        $this->expectException(GenericDiscordException::class);

        $account = Account::factory()->createQuietly(['discord_id' => 12345]);

        // All 5 attempts return 429
        Http::fakeSequence()
            ->push(['retry_after' => 0.001], 429)
            ->push(['retry_after' => 0.001], 429)
            ->push(['retry_after' => 0.001], 429)
            ->push(['retry_after' => 0.001], 429)
            ->push(['retry_after' => 0.001], 429);

        $discord = new Discord;
        $discord->grantRoleById($account, 99);
    }

    // ─── Threads ────────────────────────────────────────────────────

    #[Test]
    public function test_it_creates_a_thread_from_message_successfully()
    {
        $discord = new Discord;
        $channelId = 12345;
        $messageId = 67890;
        $data = [
            'name' => 'Test Thread',
            'auto_archive_duration' => 60,
        ];

        Http::fake([
            "discord.com/api/v10/channels/{$channelId}/messages/{$messageId}/threads" => Http::response(['id' => 'thread123', 'name' => 'Test Thread'], 200),
        ]);

        $result = $discord->createThreadFromMessage($channelId, $messageId, $data);

        $this->assertEquals('thread123', $result['id']);
        $this->assertEquals('Test Thread', $result['name']);
    }

    #[Test]
    public function test_it_throws_exception_on_thread_creation_failure()
    {
        $discord = new Discord;
        $channelId = 12345;
        $messageId = 67890;
        $data = [
            'name' => 'Test Thread',
            'auto_archive_duration' => 60,
        ];

        Http::fake([
            "discord.com/api/v10/channels/{$channelId}/messages/{$messageId}/threads" => Http::response(['message' => 'Bad Request'], 400),
        ]);

        $this->expectException(GenericDiscordException::class);
        $this->expectExceptionMessage('{"message":"Bad Request"}');

        $discord->createThreadFromMessage($channelId, $messageId, $data);
    }

    #[Test]
    public function test_softban_timeout_sends_correct_patch_request()
    {
        $account = Account::factory()->create(['discord_id' => 12345]);

        Http::fake([
            'discord.com/api/v10/guilds/*/members/12345' => Http::response([], 204),
        ]);

        $now = now();
        $expectedExpiry = $now->copy()->addDays(7);

        $discord = new Discord;
        $discord->softBan($account, 24, 7);

        Http::assertSent(function ($request) use ($expectedExpiry) {
            if ($request->method() !== 'PATCH') {
                return false;
            }

            $data = $request->data();
            if (! isset($data['communication_disabled_until'])) {
                return false;
            }

            $actualExpiry = new \DateTime($data['communication_disabled_until']);
            $diff = $expectedExpiry->diffInSeconds($actualExpiry);

            return $diff < 5;
        });
    }

    #[Test]
    public function test_softban_throws_exception_on_timeout_failure()
    {
        $this->expectException(GenericDiscordException::class);

        $account = Account::factory()->create(['discord_id' => 12345]);

        Http::fake([
            'discord.com/api/v10/guilds/*/members/12345' => Http::response(['message' => 'Missing Permissions'], 403),
        ]);

        $discord = new Discord;
        $discord->softBan($account, 24, 7);
    }

    #[Test]
    public function test_softban_purges_recent_messages()
    {
        $account = Account::factory()->create(['discord_id' => 12345]);

        // pre-populate cache
        Cache::put("discord:user:{$account->discord_id}:messages", [
            '1' => ['channel_id' => '100', 'message_id' => '1', 'cached_at' => now()->timestamp],
            '2' => ['channel_id' => '100', 'message_id' => '2', 'cached_at' => now()->timestamp],
            '3' => ['channel_id' => '200', 'message_id' => '3', 'cached_at' => now()->timestamp],
        ], 600);

        Http::fake([
            'discord.com/api/v10/guilds/*/members/12345' => Http::response([], 204),
            'discord.com/api/v10/channels/*/messages/bulk-delete' => Http::response([], 204),
            'discord.com/api/v10/channels/*/messages/*' => Http::response([], 204),
        ]);

        $discord = new Discord;
        $discord->softBan($account, 24, 7);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_contains($request->url(), '/channels/100/messages/bulk-delete')
                && $request->data() === ['messages' => ['1', '2']];
        });
        Http::assertSent(function ($request) {
            return $request->method() === 'DELETE'
                && str_contains($request->url(), '/channels/200/messages/3');
        });

        // Cache should be cleared after deletion
        $this->assertNull(Cache::get("discord:user:{$account->discord_id}:messages"));
    }

    #[Test]
    public function test_honeypot_alert_sends_message_to_mods_channel()
    {
        $account = Account::factory()->create(['discord_id' => 12345]);

        Config::set('services.discord.honeypot_channel_id', 'honeypot-123');
        Config::set('services.discord.moderators_chat_channel_id', 'mods-456');

        $discord = Mockery::mock(Discord::class, function (MockInterface $mock) use ($account) {
            $mock->shouldReceive('softBan')
                ->once()
                ->with(Mockery::on(fn ($a) => $a->is($account)), 7, 'Honeypot');

            $mock->shouldReceive('sendMessageToChannel')
                ->once()
                ->with(
                    'mods-456',
                    Mockery::on(fn (array $message) => $message['content'] === "Honeypot triggered by honeypotUser (12345) linked to account [{$account->id}](https://www.vatsim.uk/admin/accounts/{$account->id})"
                    )
                );
        });

        $service = new HoneypotService($discord);
        $service->handleTrigger(
            discordUserId: '12345',
            discordUsername: 'honeypotUser',
            messageId: '67890',
        );
    }

    #[Test]
    public function test_get_channel_messages_returns_messages()
    {
        Http::fake([
            'discord.com/api/v10/channels/100/messages*' => Http::response([
                ['id' => '1', 'channel_id' => '100', 'content' => 'hello'],
                ['id' => '2', 'channel_id' => '100', 'content' => 'world'],
            ]),
        ]);

        $messages = (new Discord)->getChannelMessages('100', 100);

        $this->assertCount(2, $messages);
        $this->assertSame('1', $messages[0]['id']);
    }

    #[Test]
    public function test_get_channel_messages_returns_empty_on_failure()
    {
        Http::fake([
            'discord.com/api/v10/channels/100/messages*' => Http::response([], 500),
        ]);

        $messages = (new Discord)->getChannelMessages('100', 100);

        $this->assertCount(0, $messages);
    }

    #[Test]
    public function test_delete_message_sends_delete_request()
    {
        Http::fake([
            'discord.com/api/v10/channels/100/messages/99' => Http::response([], 204),
        ]);

        $result = (new Discord)->deleteMessage('100', '99');

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->method() === 'DELETE'
                && str_contains($request->url(), '/channels/100/messages/99');
        });
    }

    #[Test]
    public function test_delete_message_returns_true_on_404()
    {
        Http::fake([
            'discord.com/api/v10/channels/100/messages/99' => Http::response(['message' => 'Unknown Message'], 404),
        ]);

        $result = (new Discord)->deleteMessage('100', '99');

        $this->assertTrue($result);
    }

    #[Test]
    public function test_delete_message_throws_on_api_error()
    {
        $this->expectException(GenericDiscordException::class);

        Http::fake([
            'discord.com/api/v10/channels/100/messages/99' => Http::response(['message' => 'Missing Permissions'], 403),
        ]);

        (new Discord)->deleteMessage('100', '99');
    }

    #[Test]
    public function test_bulk_delete_messages_sends_post_request_with_message_ids()
    {
        Http::fake([
            'discord.com/api/v10/channels/*/messages/bulk-delete' => Http::response([], 204),
        ]);

        $result = (new Discord)->bulkDeleteMessages('100', ['1', '2', '3']);

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && $request->data() === ['messages' => ['1', '2', '3']];
        });
    }

    #[Test]
    public function test_bulk_delete_single_message_falls_back_to_delete_message()
    {
        Http::fake([
            'discord.com/api/v10/channels/100/messages/1' => Http::response([], 204),
        ]);

        $result = (new Discord)->bulkDeleteMessages('100', ['1']);

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->method() === 'DELETE'
                && str_contains($request->url(), '/channels/100/messages/1');
        });
    }

    #[Test]
    public function test_bulk_delete_messages_throws_on_api_failure()
    {
        $this->expectException(GenericDiscordException::class);

        Http::fake([
            'discord.com/api/v10/channels/*/messages/bulk-delete' => Http::response(['message' => 'Missing Permissions'], 403),
        ]);

        (new Discord)->bulkDeleteMessages('100', ['1', '2', '3']);
    }
}
