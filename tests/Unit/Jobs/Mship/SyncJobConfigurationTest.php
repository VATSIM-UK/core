<?php

namespace Tests\Unit\Jobs\Mship;

use App\Jobs\Mship\SyncToCTS;
use App\Jobs\Mship\SyncToDiscord;
use App\Jobs\Mship\SyncToHelpdesk;
use App\Jobs\Mship\SyncToMoodle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncJobConfigurationTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    #[DataProvider('jobConfigurationProvider')]
    public function it_uses_consistent_queue_configuration(string $jobClass, string $expectedQueue): void
    {
        $job = new $jobClass($this->user);

        $this->assertSame(3, $job->tries);
        $this->assertSame(30, $job->backoff);
        $this->assertSame($expectedQueue, $job->queue);
    }

    #[Test]
    #[DataProvider('jobConfigurationProvider')]
    public function it_applies_rate_limiting_and_overlap_protection(string $jobClass, string $_expectedQueue): void
    {
        $job = new $jobClass($this->user);
        $middleware = $job->middleware();

        $this->assertCount(2, $middleware);
        $this->assertInstanceOf(RateLimitedWithRedis::class, $middleware[0]);
        $this->assertInstanceOf(WithoutOverlapping::class, $middleware[1]);
    }

    public static function jobConfigurationProvider(): array
    {
        return [
            [SyncToDiscord::class, 'discord'],
            [SyncToHelpdesk::class, 'helpdesk'],
            [SyncToMoodle::class, 'moodle'],
            [SyncToCTS::class, 'cts'],
        ];
    }
}
