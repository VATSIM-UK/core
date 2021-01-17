<?php

namespace Tests\Unit\Command;

use Tests\TestCase;
use ReflectionClass;
use App\Libraries\Discord;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Ban;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Console\Commands\ExternalServices\ManageDiscord;

class DiscordManagerTest extends TestCase
{
    use DatabaseTransactions;

    private $mockRoleId;
    private $account;

    protected function setUp() : void
    {
        parent::setUp();

        $this->mockRoleId = 122904438483;

        Config::set('services.discord.suspended_member_role_id', $this->mockRoleId);

        // discord is is not relevant; just that it is registered for completeness.
        $this->account = factory(Account::class)->create(['discord_id' => 1232]);
    }
    /** @test */
    public function itShouldCallGrantRoleWhenAccountBanned()
    {
        factory(Ban::class)->create(['account_id' => $this->account->id]);

        $mockDiscordLibrary = $this->mock(Discord::class, function ($mock) {
            $mock->shouldReceive('getUserRoles')->with($this->account)->once()->andReturn(collect([]));
            $mock->shouldReceive('grantRoleById')->with($this->account, $this->mockRoleId)->once();
        });

        $command = new ManageDiscord($mockDiscordLibrary);
        $this->setPrivatePropertyInObject($command, 'account', $this->account);
        $command->grantRoles();
    }

    /** @test */
    public function itShouldNotAddRoleWhenNotBanned()
    {
        $mockDiscordLibrary = $this->mock(Discord::class, function ($mock) {
            $mock->shouldReceive('getUserRoles')->with($this->account)->once()->andReturn(collect([]));
            $mock->shouldNotReceive('grantRoleById');
        });

        $command = new ManageDiscord($mockDiscordLibrary);
        $this->setPrivatePropertyInObject($command, 'account', $this->account);
        $command->grantRoles();
    }

    /** @test */
    public function itShouldNotAddRoleWhenStillBannedButRoleAlreadyExists()
    {
        factory(Ban::class)->create(['account_id' => $this->account->id]);

        $mockDiscordLibrary = $this->mock(Discord::class, function ($mock) {
            $mock->shouldReceive('getUserRoles')->with($this->account)->once()->andReturn(collect([$this->mockRoleId]));
            $mock->shouldNotReceive('grantRoleById');
        });

        $command = new ManageDiscord($mockDiscordLibrary);
        $this->setPrivatePropertyInObject($command, 'account', $this->account);
        $command->grantRoles();
    }

    /** @test */
    public function itShouldRemoveRoleWhenUserIsNoLongerBanned()
    {
        $mockDiscordLibrary = $this->mock(Discord::class, function ($mock) {
            // ensure the user has already been assigned the banned role.
            $mock->shouldReceive('getUserRoles')->with($this->account)->once()->andReturn(collect([$this->mockRoleId]));
            $mock->shouldReceive('removeRoleById')->with($this->account, $this->mockRoleId)->once();
        });

        $command = new ManageDiscord($mockDiscordLibrary);
        $this->setPrivatePropertyInObject($command, 'account', $this->account);
        $command->removeRoles();
    }

    /** @test */
    public function itShouldNotRemoveRoleWhenUserIsBannedAndHasRole()
    {
        factory(Ban::class)->create(['account_id' => $this->account->id]);
        $mockDiscordLibrary = $this->mock(Discord::class, function ($mock) {
            // ensure the user has already been assigned the banned role.
            $mock->shouldReceive('getUserRoles')->with($this->account)->once()->andReturn(collect([$this->mockRoleId]));
            $mock->shouldNotReceive('removeRoleById');
        });

        $command = new ManageDiscord($mockDiscordLibrary);
        $this->setPrivatePropertyInObject($command, 'account', $this->account);
        $command->removeRoles();
    }

    /**
     * Reflect a private property of a mock instance.
     *
     * @param $object
     * @param $propertyKey
     * @param $value
     * @return void
     */
    private function setPrivatePropertyInObject($object, $propertyKey, $value)
    {
        $reflection = new ReflectionClass($object);
        $propertyReflected = $reflection->getProperty($propertyKey);
        $propertyReflected->setAccessible(true);
        $propertyReflected->setValue($object, $value);
    }
}
