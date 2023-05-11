<?php

namespace Tests\Unit\Mship;

use App\Libraries\Discord;
use App\Models\Discord\DiscordRoleRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HasDiscordAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_correct_roles_assigned()
    {
        Config::set('services.discord.token', '123');

        $this->mock(Discord::class, function (MockInterface $mock) {
            $mock->shouldReceive('setNickname')->once();
            $mock->shouldReceive('getUserRoles')->andReturn(collect(['2']))->once();
            $mock->shouldReceive('grantRoleById')->with($this->user, '1')->once();
            $mock->shouldReceive('removeRoleById')->with($this->user, '2')->once();
        });

        $permissionHas = factory(Permission::class)->create();
        DiscordRoleRule::factory()->create(['discord_id' => '3', 'permission_id' => factory(Permission::class)->create()]); // Role rule means user shouldn't have role

        DiscordRoleRule::factory()->create(['discord_id' => '1', 'permission_id' => $permissionHas]); // Role rule means user should have
        DiscordRoleRule::factory()->create(['discord_id' => '1', 'permission_id' => factory(Permission::class)->create()]); // Role rule means user shouldn't have (but granted by one above)

        DiscordRoleRule::factory()->create(['discord_id' => '2', 'permission_id' => factory(Permission::class)->create()]); // Role rule means user shouldn't have role. Should be removed


        $this->user->givePermissionTo($permissionHas);

        $this->user->syncToDiscord();
    }
}
