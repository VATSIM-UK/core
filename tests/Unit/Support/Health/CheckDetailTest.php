<?php

namespace Tests\Unit\Support\Health;

use App\Support\Health\CheckDetail;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Health\ResultStores\StoredCheckResults\StoredCheckResult;
use Tests\TestCase;

class CheckDetailTest extends TestCase
{
    #[Test]
    public function it_reports_reachability_for_connection_checks(): void
    {
        foreach (['database', 'database_cts', 'redis'] as $name) {
            $this->assertSame('connected', CheckDetail::for(StoredCheckResult::make(name: $name, status: 'ok')));
            $this->assertSame('degraded', CheckDetail::for(StoredCheckResult::make(name: $name, status: 'warning')));
            $this->assertSame('unreachable', CheckDetail::for(StoredCheckResult::make(name: $name, status: 'failed')));
            $this->assertSame('unreachable', CheckDetail::for(StoredCheckResult::make(name: $name, status: 'crashed')));
        }
    }

    #[Test]
    public function it_uses_the_horizon_summary(): void
    {
        $this->assertSame('running', CheckDetail::for(StoredCheckResult::make(name: 'horizon', status: 'ok', shortSummary: 'Running')));
        $this->assertSame('paused', CheckDetail::for(StoredCheckResult::make(name: 'horizon', status: 'warning', shortSummary: 'Paused')));
        $this->assertSame('not running', CheckDetail::for(StoredCheckResult::make(name: 'horizon', status: 'failed', shortSummary: 'Not running')));
    }

    #[Test]
    public function it_reports_disk_usage_as_a_percentage(): void
    {
        $result = StoredCheckResult::make(
            name: 'disk',
            status: 'warning',
            shortSummary: '78%',
            meta: ['disk_space_used_percentage' => 78],
        );

        $this->assertSame('78% used', CheckDetail::for($result));
    }

    #[Test]
    public function it_reports_schedule_heartbeat_age(): void
    {
        $this->freezeTime();

        cache()->put('health:checks:schedule:latestHeartbeatAt', now()->subMinutes(36)->timestamp);
        $this->assertSame('last ran 36m ago', CheckDetail::for(StoredCheckResult::make(name: 'schedule', status: 'failed')));

        cache()->put('health:checks:schedule:latestHeartbeatAt', now()->subSeconds(20)->timestamp);
        $this->assertSame('last ran just now', CheckDetail::for(StoredCheckResult::make(name: 'schedule', status: 'ok')));

        cache()->forget('health:checks:schedule:latestHeartbeatAt');
        $this->assertSame('no heartbeat yet', CheckDetail::for(StoredCheckResult::make(name: 'schedule', status: 'failed')));
    }

    #[Test]
    public function it_uses_the_scheduled_tasks_summary(): void
    {
        $this->assertSame('all ok', CheckDetail::for(StoredCheckResult::make(name: 'scheduled_tasks', status: 'ok', shortSummary: 'all ok')));
        $this->assertSame('2 failed: a, b', CheckDetail::for(StoredCheckResult::make(name: 'scheduled_tasks', status: 'failed', shortSummary: '2 failed: a, b')));
    }

    #[Test]
    public function it_reports_no_heartbeat_when_the_cache_store_is_unreachable(): void
    {
        config([
            'cache.default' => 'broken',
            'cache.stores.broken' => ['driver' => 'nonsense'],
        ]);

        $this->assertSame('no heartbeat yet', CheckDetail::for(StoredCheckResult::make(name: 'schedule', status: 'failed')));
    }

    #[Test]
    public function it_falls_back_to_the_status_for_unknown_checks(): void
    {
        $this->assertSame('failed', CheckDetail::for(StoredCheckResult::make(name: 'something_new', status: 'failed')));
    }
}
