<?php

namespace Tests;

use Carbon\Carbon;
use App\Models\Mship\Account;
use Spatie\Permission\Models\Role;
use App\Models\Cts\MockCtsDatabase;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /* @var Carbon */
    protected $knownDate;

    protected $privacc;

    protected function setUp()
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);

        config(['app.url' => 'http://'.config('app.url')]);

        Carbon::setTestNow();
        $this->knownDate = Carbon::now();

        $this->seedLegacyTables();

        app()['cache']->forget('spatie.permission.cache');
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();
        $this->setUpPrivacc();
    }

    protected function setUpPrivacc()
    {
        $privaccHolder = factory(Account::class)->create();
        $privaccHolder->assignRole(Role::findByName('privacc'));
        $this->privacc = $privaccHolder->fresh();
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
}
