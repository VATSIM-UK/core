<?php

declare(strict_types=1);

namespace Tests\Unit\Command;

use App\Libraries\Discord;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncDiscordTagsCommandTest extends TestCase
{
    #[Test]
    public function it_calls_sync_tag_commands()
    {
        $discord = \Mockery::mock(Discord::class);
        $discord->shouldReceive('syncTagCommands')
            ->once();

        app()->instance(Discord::class, $discord);

        $this->artisan('discord:sync-tags')
            ->expectsOutput('Syncing Discord tags...')
            ->expectsOutput('Done.')
            ->assertExitCode(0);
    }
}
