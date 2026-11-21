<?php

namespace Tests\Feature\Health;

use PHPUnit\Framework\Attributes\Test;
use Spatie\Health\Facades\Health;
use Tests\TestCase;

class HealthChecksRegisteredTest extends TestCase
{
    #[Test]
    public function it_registers_the_expected_health_checks(): void
    {
        $this->assertSame(
            ['database', 'database_cts', 'redis', 'horizon', 'schedule', 'scheduled_tasks', 'disk'],
            Health::registeredChecks()->map->getName()->all(),
        );
    }
}
