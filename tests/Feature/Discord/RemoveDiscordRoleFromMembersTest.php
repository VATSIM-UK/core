<?php

declare(strict_types=1);

namespace Tests\Feature\Discord;

use App\Jobs\Discord\RemoveDiscordRoleFromMembers;
use App\Models\Discord\DiscordRoleRule;
use App\Models\Mship\Account;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RemoveDiscordRoleFromMembersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.discord.token' => 'test-token',
            'services.discord.guild_id' => '12345',
            'services.discord.base_discord_uri' => 'https://discord.com/api/v10',
        ]);
    }

    #[Test]
    public function it_removes_the_role_from_every_discord_linked_account(): void
    {
        Http::fake([
            'discord.com/api/v10/guilds/12345/members/*/roles/999' => Http::response(null, 204),
        ]);

        $a = Account::factory()->createQuietly(['discord_id' => '111111111111111111']);
        $b = Account::factory()->createQuietly(['discord_id' => '222222222222222222']);
        Account::factory()->createQuietly(['discord_id' => null]);

        (new RemoveDiscordRoleFromMembers(999))->handle();

        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && $request->url() === "https://discord.com/api/v10/guilds/12345/members/{$a->discord_id}/roles/999");
        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && $request->url() === "https://discord.com/api/v10/guilds/12345/members/{$b->discord_id}/roles/999");
        Http::assertSentCount(2);
    }

    #[Test]
    public function deleting_a_rule_queues_removal_of_its_role(): void
    {
        Queue::fake();

        $rule = DiscordRoleRule::factory()->create(['discord_id' => '999']);

        $rule->delete();

        Queue::assertPushed(
            RemoveDiscordRoleFromMembers::class,
            fn (RemoveDiscordRoleFromMembers $job) => $job->getRoleId() === 999,
        );
    }
}
