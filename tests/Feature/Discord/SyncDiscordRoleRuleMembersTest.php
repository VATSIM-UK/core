<?php

declare(strict_types=1);

namespace Tests\Feature\Discord;

use App\Jobs\Discord\SyncDiscordRoleRuleMembers;
use App\Jobs\Mship\SyncToDiscord;
use App\Models\Atc\Position;
use App\Models\Discord\DiscordRoleRule;
use App\Models\Mship\Account;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SyncDiscordRoleRuleMembersTest extends TestCase
{
    #[Test]
    public function it_dispatches_a_discord_sync_only_for_matching_accounts(): void
    {
        Queue::fake();

        $permission = Permission::firstOrCreate(['name' => 'discord.test.job', 'guard_name' => 'web']);
        $rule = DiscordRoleRule::factory()->create(['permission_id' => $permission->id]);

        $matching = Account::factory()->createQuietly(['discord_id' => '111111111']);
        $matching->givePermissionTo($permission);

        $nonMatching = Account::factory()->createQuietly(['discord_id' => '222222222']);

        (new SyncDiscordRoleRuleMembers($rule->getKey()))->handle();

        Queue::assertPushed(SyncToDiscord::class, fn (SyncToDiscord $job) => $job->getAccountId() === $matching->id);
        Queue::assertNotPushed(SyncToDiscord::class, fn (SyncToDiscord $job) => $job->getAccountId() === $nonMatching->id);
    }

    #[Test]
    public function it_does_nothing_when_the_rule_no_longer_exists(): void
    {
        Queue::fake();

        (new SyncDiscordRoleRuleMembers(PHP_INT_MAX))->handle();

        Queue::assertNothingPushed();
    }

    #[Test]
    public function it_does_not_throw_for_a_position_endorsement_rule_when_a_candidate_has_no_roster_row(): void
    {
        Queue::fake();

        $position = Position::factory()->create(['type' => Position::TYPE_ENROUTE]);

        $rule = DiscordRoleRule::factory()->create();
        $rule->endorsable()->associate($position);
        $rule->save();

        Account::factory()->createQuietly(['discord_id' => '333333333']);

        (new SyncDiscordRoleRuleMembers($rule->getKey()))->handle();

        Queue::assertNotPushed(SyncToDiscord::class);
    }
}
