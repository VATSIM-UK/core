<?php

namespace Tests\Feature\Health;

use Carbon\Carbon;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Spatie\Health\Facades\Health;
use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    #[Test]
    public function test_up_returns_ok(): void
    {
        $this->get('/up')
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }

    #[Test]
    public function test_health_returns_each_check(): void
    {
        cache()->put('health:checks:schedule:latestHeartbeatAt', now()->timestamp);

        $this->registerChecks(
            $this->check('database', Result::make()->ok()),
            $this->check('database_cts', Result::make()->ok()),
            $this->check('redis', Result::make()->ok()),
            $this->check('horizon', Result::make()->ok()->shortSummary('Running')),
            $this->check('schedule', Result::make()->ok()),
            $this->check('disk', Result::make()->ok()->meta(['disk_space_used_percentage' => 42])),
        );

        $response = $this->get('/health');

        $response->assertOk();
        $this->assertSame(['status', 'finished_at', 'checks'], array_keys($response->json()));
        $this->assertSame('ok', $response->json('status'));

        $checks = $response->json('checks');
        $this->assertSame(
            ['database', 'database_cts', 'redis', 'horizon', 'schedule', 'disk'],
            array_column($checks, 'name'),
        );

        foreach ($checks as $check) {
            $this->assertSame(['name', 'status', 'detail'], array_keys($check));
        }

        $this->assertSame(
            ['connected', 'connected', 'connected', 'running', 'last ran just now', '42% used'],
            array_column($checks, 'detail'),
        );

        $this->assertNotNull(Carbon::parse($response->json('finished_at')));
    }

    #[Test]
    public function test_a_warning_is_not_a_failure(): void
    {
        $this->registerChecks(
            $this->check('database', Result::make()->ok()),
            $this->check('disk', Result::make()->warning('full')->meta(['disk_space_used_percentage' => 78])),
        );

        $response = $this->get('/health');

        $response->assertOk();
        $this->assertSame('warning', $response->json('status'));
        $this->assertSame('warning', $response->json('checks.1.status'));
        $this->assertSame('78% used', $response->json('checks.1.detail'));
    }

    #[Test]
    public function test_a_failed_check_returns_503(): void
    {
        $this->registerChecks(
            $this->check('database', Result::make()->ok()),
            $this->check('redis', Result::make()->failed('down')),
        );

        $response = $this->get('/health');

        $response->assertStatus(503);
        $this->assertSame('failing', $response->json('status'));
        $this->assertSame('failed', $response->json('checks.1.status'));
        $this->assertSame('unreachable', $response->json('checks.1.detail'));
    }

    #[Test]
    public function test_a_crashed_check_returns_json_not_a_500(): void
    {
        $throwing = new class extends Check
        {
            public function run(): Result
            {
                throw new RuntimeException('boom');
            }
        };
        $throwing->name('database');

        $this->registerChecks(
            $throwing,
            $this->check('redis', Result::make()->ok()),
        );

        $response = $this->get('/health');

        $response->assertStatus(503);
        $this->assertSame('failing', $response->json('status'));
        $this->assertSame('crashed', $response->json('checks.0.status'));
        $this->assertSame('unreachable', $response->json('checks.0.detail'));
        $this->assertSame(2, count($response->json('checks')));
    }

    #[Test]
    public function test_the_response_never_leaks_package_messages_or_meta(): void
    {
        $this->registerChecks(
            $this->check('database', Result::make()->failed("Access denied for user 'core'@'10.0.1.5'")),
        );

        $response = $this->get('/health');
        $body = $response->getContent();

        $response->assertStatus(503);
        $this->assertStringNotContainsString('10.0.1.5', $body);
        $this->assertStringNotContainsString('Access denied', $body);
        $this->assertStringNotContainsString('notificationMessage', $body);
        $this->assertStringNotContainsString('"meta"', $body);
        $this->assertSame(['name', 'status', 'detail'], array_keys($response->json('checks.0')));
    }

    #[Test]
    public function test_health_route_has_no_web_or_session_middleware(): void
    {
        $route = Route::getRoutes()->getByName('health.index');

        $this->assertNotNull($route);
        $this->assertSame(['throttle:30,1'], $route->gatherMiddleware());
    }

    #[Test]
    public function test_a_missing_schedule_heartbeat_fails_without_throwing(): void
    {
        cache()->forget('health:checks:schedule:latestHeartbeatAt');

        $this->registerChecks($this->check('schedule', Result::make()->failed('The schedule did not run yet.')));

        $response = $this->get('/health');

        $response->assertStatus(503);
        $this->assertSame('failing', $response->json('status'));
        $this->assertSame('failed', $response->json('checks.0.status'));
        $this->assertSame('no heartbeat yet', $response->json('checks.0.detail'));
    }

    #[Test]
    public function test_the_endpoints_do_not_depend_on_the_default_cache_store(): void
    {
        config([
            'cache.default' => 'broken',
            'cache.stores.broken' => ['driver' => 'nonsense'],
        ]);
        $this->app->forgetInstance(RateLimiter::class);

        $this->get('/up')->assertOk()->assertExactJson(['status' => 'ok']);
        $this->get('/health')->assertStatus(503)->assertHeader('Content-Type', 'application/json');
    }

    private function registerChecks(Check ...$checks): void
    {
        Health::clearChecks()->checks($checks);
    }

    private function check(string $name, Result $result): Check
    {
        $check = new class extends Check
        {
            public Result $result;

            public function run(): Result
            {
                return $this->result;
            }
        };

        $check->name($name);
        $check->result = $result;

        return $check;
    }
}
