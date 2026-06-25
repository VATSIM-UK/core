<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\Discord;

use App\Jobs\Discord\SyncDiscordTags;
use App\Libraries\Discord;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncDiscordTagsTest extends TestCase
{
    #[Test]
    public function it_is_queued_on_the_discord_queue()
    {
        Queue::fake();

        SyncDiscordTags::dispatch();

        Queue::assertPushedOn('discord', SyncDiscordTags::class);
    }

    #[Test]
    public function it_has_three_retry_attempts()
    {
        $job = new SyncDiscordTags;

        $this->assertSame(3, $job->tries);
    }

    #[Test]
    public function it_calls_sync_tag_commands_on_the_discord_library()
    {
        $discord = \Mockery::mock(Discord::class);
        $discord->shouldReceive('syncTagCommands')
            ->once();

        $job = new SyncDiscordTags;
        $job->handle($discord);
    }
}
