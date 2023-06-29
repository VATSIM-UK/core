<?php

namespace Tests;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;
    use CreatesApplication;

    protected $knownDate;

    private $privacc;

    private $user;

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

        $now = now()->setMicro(0);
        Carbon::setTestNow($now);
        $this->knownDate = $now;

        // Force regeneration of permissions cache
        app()['cache']->forget('spatie.permission.cache');
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();

        \Illuminate\Support\Facades\Notification::fake();
    }

    public function __get($name)
    {
        if ($name === 'privacc') {
            return $this->getOrMakePrivaccUser();
        }
        if ($name === 'user') {
            return $this->getOrMakeUser();
        }
    }

    protected function getOrMakeUser(): Account
    {
        if ($this->user) {
            return $this->user;
        }

        return $this->user = Account::factory()->withQualification()->create();
    }

    protected function getOrMakePrivaccUser(): Account
    {
        if ($this->privacc) {
            return $this->privacc;
        }

        $user = Account::factory()->withQualification()->create();
        $role = Role::findByName('privacc');
        $role->givePermissionTo('*');
        $user->assignRole($role);

        return $this->privacc = $user->fresh();
    }

    public function markNovaTest()
    {
        if (! class_exists('\Laravel\Nova\Nova')) {
            $this->markTestSkipped('Nova is required to pass test.');
        }
    }
}
