<?php

declare(strict_types=1);

namespace Tests\Unit\Discord;

use App\Libraries\Discord;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DiscordGuildRolesTest extends TestCase
{
    private const GUILD_ROLES_URL = 'discord.com/api/v10/guilds/12345/roles';

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        config([
            'services.discord.token' => 'test-token',
            'services.discord.guild_id' => '12345',
            'services.discord.base_discord_uri' => 'https://discord.com/api/v10',
        ]);
    }

    #[Test]
    public function it_fetches_and_caches_the_guild_roles(): void
    {
        Http::fake([
            self::GUILD_ROLES_URL => Http::response([
                ['id' => '111', 'name' => 'Tower Student', 'color' => 16711680],
                ['id' => '222', 'name' => 'Enroute', 'color' => 0],
            ]),
        ]);

        $discord = new Discord;

        $first = $discord->getGuildRoles();
        $second = $discord->getGuildRoles();

        $this->assertCount(2, $first);
        $this->assertSame($first->all(), $second->all());
        Http::assertSentCount(1);
    }

    #[Test]
    public function it_does_not_cache_a_failed_request(): void
    {
        Http::fake([
            self::GUILD_ROLES_URL => Http::response(['message' => 'boom'], 500),
        ]);

        $discord = new Discord;

        $this->assertCount(0, $discord->getGuildRoles());
        $this->assertCount(0, $discord->getGuildRoles());

        Http::assertSentCount(2);
    }

    #[Test]
    public function it_returns_empty_without_calling_the_api_when_discord_is_not_configured(): void
    {
        config(['services.discord.token' => null]);

        Http::fake();

        $discord = new Discord;

        $this->assertCount(0, $discord->getGuildRoles());
        Http::assertNothingSent();
    }

    #[Test]
    public function it_returns_empty_when_the_request_throws(): void
    {
        Http::fake(function () {
            throw new ConnectionException('unreachable');
        });

        $discord = new Discord;

        $this->assertCount(0, $discord->getGuildRoles());
    }
}
