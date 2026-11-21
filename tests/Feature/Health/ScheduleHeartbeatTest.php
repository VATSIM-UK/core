<?php

namespace Tests\Feature\Health;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ScheduleHeartbeatTest extends TestCase
{
    #[Test]
    public function it_runs_the_health_heartbeat_every_minute(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('health:schedule-check-heartbeat')
            ->assertSuccessful();
    }
}
