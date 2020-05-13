<?php

namespace Tests;

use App\Models\Sys\Notification;
use Carbon\Carbon;
use App\Models\Mship\Account;
use Spatie\Permission\Models\Role;
use Tests\Database\MockCtsDatabase;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $knownDate;

    protected $privacc;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create global super admin for testing
        $this->createPrivaccUser();

        // Create generic user
        $this->user = factory(Account::class)->create();
    }

    protected function createPrivaccUser()
    {
        $user = factory(Account::class)->create();
        $user->assignRole(Role::findByName('privacc'));
        $this->privacc = $user->fresh();
    }

    protected function seedLegacyTables()
    {
        if (!method_exists($this, 'beginDatabaseTransaction')) {
            return;
        }

        $this->dropLegacyTables();

        MockCtsDatabase::create();
    }

    protected function dropLegacyTables()
    {
        if (!method_exists($this, 'beginDatabaseTransaction')) {
            return;
        }

        MockCtsDatabase::destroy();
    }

    public function markNovaTest()
    {
        if (!class_exists('\Laravel\Nova\Nova')) {
            $this->markTestSkipped('Nova is required to pass test.');
        }
    }
}
