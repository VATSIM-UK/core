<?php

namespace Tests;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;
use Tests\Database\MockCtsDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $knownDate;

    protected $privacc;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Exclude Middleware Across All Tests
        $this->withoutMiddleware(VerifyCsrfToken::class);

        // Add HTTP protocol
        $parsed = parse_url(config('app.url'));
        if (empty($parsed['scheme'])) {
            config(['app.url' => 'http://'.config('app.url')]);
        }

        Carbon::setTestNow(Carbon::now());
        $this->knownDate = Carbon::now();

        // Seed
        $this->seed();

        // Create tables for other services
        $this->seedLegacyTables();

        // Force regeneration of permissions cache
        app()['cache']->forget('spatie.permission.cache');
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();

        \Illuminate\Support\Facades\Notification::fake();
        // Create global super admin for testing
        $this->createPrivaccUser();

        // Create generic user
        $this->user = Account::factory()->withQualification()->create();
    }

    protected function createPrivaccUser()
    {
        $user = Account::factory()->withQualification()->create();
        $role = Role::findByName('privacc');
        $role->givePermissionTo('*');
        $user->assignRole($role);
        $this->privacc = $user->fresh();
    }

    protected function seedLegacyTables()
    {
        if (! method_exists($this, 'beginDatabaseTransaction')) {
            return;
        }

        $this->dropLegacyTables();

        MockCtsDatabase::create();
    }

    protected function dropLegacyTables()
    {
        if (! method_exists($this, 'beginDatabaseTransaction')) {
            return;
        }

        MockCtsDatabase::destroy();
    }

    public function markNovaTest()
    {
        if (! class_exists('\Laravel\Nova\Nova')) {
            $this->markTestSkipped('Nova is required to pass test.');
        }
    }
}
