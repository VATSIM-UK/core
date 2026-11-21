<?php

namespace Tests\Unit\Support\Health;

use App\Support\Health\ScheduledTasksCheck;
use PHPUnit\Framework\Attributes\Test;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTask;
use Tests\TestCase;

class ScheduledTasksCheckTest extends TestCase
{
    #[Test]
    public function it_passes_when_no_task_has_failed(): void
    {
        $this->task(['last_finished_at' => now()]);
        $this->task(['name' => 'never:run']);

        $result = ScheduledTasksCheck::new()->run();

        $this->assertSame('ok', $result->status->value);
        $this->assertSame('all ok', $result->shortSummary);
    }

    #[Test]
    public function it_fails_when_the_last_run_failed(): void
    {
        $this->task([
            'name' => 'helpdesk:sync-staff',
            'last_finished_at' => now()->subHour(),
            'last_failed_at' => now(),
        ]);

        $result = ScheduledTasksCheck::new()->run();

        $this->assertSame('failed', $result->status->value);
        $this->assertSame('1 failed: helpdesk:sync-staff', $result->shortSummary);
    }

    #[Test]
    public function it_passes_when_a_failed_task_later_succeeded(): void
    {
        $this->task([
            'last_failed_at' => now()->subHour(),
            'last_finished_at' => now(),
        ]);

        $this->assertSame('ok', ScheduledTasksCheck::new()->run()->status->value);
    }

    #[Test]
    public function it_fails_when_a_task_failed_without_ever_finishing(): void
    {
        $this->task(['last_failed_at' => now()]);

        $this->assertSame('failed', ScheduledTasksCheck::new()->run()->status->value);
    }

    private function task(array $attributes = []): MonitoredScheduledTask
    {
        return MonitoredScheduledTask::create(array_merge([
            'name' => 'test:command',
            'cron_expression' => '* * * * *',
            'grace_time_in_minutes' => 5,
        ], $attributes));
    }
}
