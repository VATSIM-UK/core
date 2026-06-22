<?php

namespace Tests\Unit\Mship;

use App\Events\Discord\DiscordUnlinked;
use App\Libraries\Discord;
use App\Models\Discord\DiscordRoleRule;
use App\Models\Mship\Account;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HasDiscordAccountTest extends TestCase
{
    public function test_correct_roles_assigned()
    {
        Config::set('services.discord.token', '123');

        $this->mock(Discord::class, function (MockInterface $mock) {
            $mock->shouldReceive('setNickname')->once();
            $mock->shouldReceive('getUserRoles')->andReturn(collect(['2']))->once();
            $mock->shouldReceive('setRoles')->with($this->user, ['1'])->once();
        });

        $permissionHas = factory(Permission::class)->create(['name' => 'discord.test.role-1']);
        $missingPermissionForRole2And3 = factory(Permission::class)->create(['name' => 'discord.test.role-2']);
        $missingPermissionForRole1Alternative = factory(Permission::class)->create(['name' => 'discord.test.role-3']);

        DiscordRoleRule::factory()->create(['discord_id' => '3', 'permission_id' => $missingPermissionForRole2And3]); // Role rule means user shouldn't have role

        DiscordRoleRule::factory()->create(['discord_id' => '1', 'permission_id' => $permissionHas]); // Role rule means user should have
        DiscordRoleRule::factory()->create(['discord_id' => '1', 'permission_id' => $missingPermissionForRole1Alternative]); // Role rule means user shouldn't have (but granted by one above)

        DiscordRoleRule::factory()->create(['discord_id' => '2', 'permission_id' => $missingPermissionForRole2And3]); // Role rule means user shouldn't have role. Should be removed

        $this->user->syncPermissions([$permissionHas]);

        $this->user->syncToDiscord();
    }

    public function test_sync_to_discord_dispatches_discord_unlinked_when_nickname_unknown_member(): void
    {
        Event::fake([DiscordUnlinked::class]);
        Config::set('services.discord.token', 'test-token');
        Config::set('services.discord.guild_id', 123);

        $account = Account::factory()->createQuietly(['discord_id' => 12345]);

        Http::fake([
            'discord.com/api/v6/guilds/*/members/12345' => Http::response(['message' => 'Unknown Member'], 404),
        ]);

        $account->syncToDiscord();

        Event::assertDispatched(DiscordUnlinked::class, fn (DiscordUnlinked $event) => $event->account->is($account));
    }

    public function test_sync_to_discord_dispatches_discord_unlinked_when_set_roles_unknown_member(): void
    {
        Event::fake([DiscordUnlinked::class]);
        Config::set('services.discord.token', 'test-token');
        Config::set('services.discord.guild_id', 123);

        $permissionHas = factory(Permission::class)->create(['name' => 'discord.test.role-1']);
        $missingPermission = factory(Permission::class)->create(['name' => 'discord.test.role-2']);

        DiscordRoleRule::factory()->create(['discord_id' => '1', 'permission_id' => $permissionHas]);
        DiscordRoleRule::factory()->create(['discord_id' => '2', 'permission_id' => $missingPermission]);

        $account = Account::factory()->createQuietly(['discord_id' => 12345]);
        $account->syncPermissions([$permissionHas]);

        Http::fake([
            'discord.com/api/v6/guilds/*/members/12345' => Http::sequence()
                ->push([], 204)
                ->push(['roles' => ['2']], 200)
                ->push(['message' => 'Unknown Member'], 404),
        ]);

        $account->syncToDiscord();

        Event::assertDispatched(DiscordUnlinked::class, fn (DiscordUnlinked $event) => $event->account->is($account));
    }

    public function test_includes_cid_in_name()
    {
        $user = Account::factory()->createQuietly([
            'name_first' => 'Test',
            'name_last' => 'Name',
            'id' => 123456789,
        ]);

        $this->assertEquals('Test Name - 123456789', $user->discordName);
    }

    public function test_include_cid_in_name_when_name_too_long()
    {
        $user = Account::factory()->createQuietly([
            'name_first' => 'Test',
            'name_last' => 'Name',
            'id' => 123456789,
        ]);

        $user->name_last = 'This is a very long name that is over 32 characters long';
        // takes first character of last name
        $this->assertEquals('Test T - 123456789', $user->discordName);
        $this->assertLessThanOrEqual(32, strlen($user->discordName));
    }

    public function test_longer_names_with_middle_name()
    {
        $user = Account::factory()->createQuietly([
            'name_first' => 'The Peoples Front',
            'name_last' => 'Judea',
            'id' => 123456789,
        ]);

        $this->assertEquals('The Peoples Front J - 123456789', $user->discordName);
        $this->assertLessThanOrEqual(32, strlen($user->discordName));
    }

    public function test_longer_first_names_with_middle_name()
    {
        $user = Account::factory()->createQuietly([
            'name_first' => 'My Very Long First Name That Exceeds The Limit',
            'name_last' => 'Another Very Long Last Name That Exceeds The Limit',
            'id' => 123456789,
        ]);

        $this->assertEquals('My Very Long First A - 123456789', $user->discordName);
        $this->assertLessThanOrEqual(32, strlen($user->discordName));
    }

    public function test_supertruncate()
    {
        $user = Account::factory()->createQuietly([
            'name_first' => 'MyVeryLongFirstNameThatExceedsTheLimit',
            'name_last' => 'AnotherVeryLongLastNameThatExceedsTheLimit',
            'id' => 123456789,
        ]);
        $this->assertEquals('MyVeryLongFirstNam A - 123456789', $user->discordName);
        $this->assertLessThanOrEqual(32, strlen($user->discordName));
    }

    public function test_banned_user_with_booster_role_preserves_booster_role()
    {
        Config::set('services.discord.token', '123');
        Config::set('services.discord.suspended_member_role_id', 'suspended');
        Config::set('services.discord.booster_role_id', 'booster');

        $this->user->discord_id = 12345;

        // Give the user an active ban
        $banReason = \App\Models\Mship\Ban\Reason::factory()->create(['period_amount' => 1, 'period_unit' => 'D']);
        $this->user->addBan($banReason, 'Test ban reason', 'Test internal note', $this->privacc);

        $this->assertTrue($this->user->isBanned);

        $this->mock(Discord::class, function (MockInterface $mock) {
            $mock->shouldReceive('setNickname')->once();
            $mock->shouldReceive('getUserRoles')->andReturn(collect(['some_role', 'booster']))->once();
            $mock->shouldReceive('setRoles')->with($this->user, ['suspended', 'booster'])->once();
        });

        $this->user->syncToDiscord();
    }

    public function test_banned_user_without_booster_role_gets_only_suspended_role()
    {
        Config::set('services.discord.token', '123');
        Config::set('services.discord.suspended_member_role_id', 'suspended');
        Config::set('services.discord.booster_role_id', 'booster');

        $this->user->discord_id = 12345;

        // Give the user an active ban
        $banReason = \App\Models\Mship\Ban\Reason::factory()->create(['period_amount' => 1, 'period_unit' => 'D']);
        $this->user->addBan($banReason, 'Test ban reason', 'Test internal note', $this->privacc);

        $this->assertTrue($this->user->isBanned);

        $this->mock(Discord::class, function (MockInterface $mock) {
            $mock->shouldReceive('setNickname')->once();
            $mock->shouldReceive('getUserRoles')->andReturn(collect(['some_role', 'other_role']))->once();
            $mock->shouldReceive('setRoles')->with($this->user, ['suspended'])->once();
        });

        $this->user->syncToDiscord();
    }

    public function test_banned_user_already_in_suspended_role_does_not_update_roles()
    {
        Config::set('services.discord.token', '123');
        Config::set('services.discord.suspended_member_role_id', 'suspended');
        Config::set('services.discord.booster_role_id', 'booster');

        $this->user->discord_id = 12345;

        // Give the user an active ban
        $banReason = \App\Models\Mship\Ban\Reason::factory()->create(['period_amount' => 1, 'period_unit' => 'D']);
        $this->user->addBan($banReason, 'Test ban reason', 'Test internal note', $this->privacc);

        $this->assertTrue($this->user->isBanned);

        $this->mock(Discord::class, function (MockInterface $mock) {
            $mock->shouldReceive('setNickname')->once();
            $mock->shouldReceive('getUserRoles')->andReturn(collect(['suspended', 'booster']))->once();
            $mock->shouldNotReceive('setRoles');
        });

        $this->user->syncToDiscord();
    }
}
