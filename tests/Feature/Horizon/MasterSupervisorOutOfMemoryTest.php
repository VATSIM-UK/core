<?php

namespace Tests\Feature\Horizon;

use App\Libraries\Discord;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Laravel\Horizon\Events\MasterSupervisorOutOfMemory;
use Laravel\Horizon\MasterSupervisor;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MasterSupervisorOutOfMemoryTest extends TestCase
{
    #[Test]
    public function it_notifies_bugsnag_once_per_throttle_window()
    {
        Bugsnag::shouldReceive('notifyError')
            ->once()
            ->withArgs(function (string $name, string $message, callable $callback) {
                $this->assertSame('Horizon master out of memory', $name);
                $this->assertStringContainsString('exceeded its memory limit', $message);

                return true;
            });

        $master = new MasterSupervisor('testing');

        event(new MasterSupervisorOutOfMemory($master));
        event(new MasterSupervisorOutOfMemory($master));
    }

    #[Test]
    public function it_pings_the_technology_role_in_discord_once_per_throttle_window()
    {
        config([
            'services.discord.critical_alerts_channel_id' => '999',
            'services.discord.technology_role_id' => '888',
        ]);

        Bugsnag::shouldReceive('notifyError')->once();

        $this->mock(Discord::class)
            ->shouldReceive('sendMessageToChannel')
            ->once()
            ->withArgs(function (string $channelId, array $payload) {
                $this->assertSame('999', $channelId);
                $this->assertStringContainsString('<@&888>', $payload['content']);
                $this->assertSame(['parse' => [], 'roles' => ['888']], $payload['allowed_mentions']);

                return true;
            });

        $master = new MasterSupervisor('testing');

        event(new MasterSupervisorOutOfMemory($master));
        event(new MasterSupervisorOutOfMemory($master));
    }
}
