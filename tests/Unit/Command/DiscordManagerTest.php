<?php

namespace Tests\Unit\Command;

use App\Console\Commands\ExternalServices\ManageDiscord;
use App\Libraries\Discord;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DiscordManagerTest extends TestCase
{
    use DatabaseTransactions;

    private $mockRoleId;
    private $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRoleId = 122904438483;

        Config::set('services.discord.suspended_member_role_id', $this->mockRoleId);

        // Discord id is not relevant; just that it is registered for completeness.
        $this->account = factory(Account::class)->create(['discord_id' => 1232]);
    }

    /** @test */
    public function itRemovesExistingRolesAndAddSuspendedRole()
    {
        $roles = collect([
            392039,
            348344,
        ]);

        $mockDiscordLibrary = $this->mock(Discord::class, function ($mock) use ($roles) {
            // collection represents random set of roles which need to be removed from a suspended user.
            $mock->shouldReceive('getUserRoles')->with($this->account)->once()->andReturn($roles);
            $mock->shouldReceive('grantRoleById')->with($this->account, $this->mockRoleId)->once();
            $mock->shouldReceive('removeRoleById')->times($roles->count());
        });

        $command = new ManageDiscord($mockDiscordLibrary);
        $command->processSuspendedMember($this->account);
    }

    /** @test */
    public function itShouldRemoveBannedUserRoleWhenRemoveRolesInvoked()
    {
        $roles = collect([$this->mockRoleId]);

        $mockDiscordLibrary = $this->mock(Discord::class, function ($mock) use ($roles) {
            // collection represents random set of roles which need to be removed from a suspended user.
            $mock->shouldReceive('getUserRoles')->with($this->account)->once()->andReturn($roles);
            $mock->shouldReceive('removeRoleById')->with($this->account, $this->mockRoleId)->once();
        });

        $command = new ManageDiscord($mockDiscordLibrary);
        $command->removeRoles($this->account);
    }

    /** @test */
    public function itShouldDoNothingIfAlreadyContainsSuspendedRole()
    {
        $mockDiscordLibrary = $this->mock(Discord::class, function ($mock) {
            // collection represents random set of roles which need to be removed from a suspended user.
            $mock->shouldReceive('getUserRoles')->with($this->account)->once()->andReturn(collect([$this->mockRoleId]));
            $mock->shouldNotReceive('removeRoleById');
        });

        $command = new ManageDiscord($mockDiscordLibrary);
        $command->processSuspendedMember($this->account);
    }
}
