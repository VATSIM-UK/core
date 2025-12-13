<?php

namespace Tests;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseTransactions;

    protected $knownDate;

    private $privacc;

    private $user;

    protected $connectionsToTransact = [null, 'cts']; // Default and CTS database connections

    /**
     * Track if the database has been seeded for this test run
     * This allows us to seed once and reuse the data across all tests
     */
    protected static $seeded = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed once per test run (not per test)
        // We commit the seed data so it persists, then start a new transaction for test data
        if (! self::$seeded) {
            $this->seed();

            // Commit the seed data (roles, permissions) so it persists across tests
            foreach ($this->connectionsToTransact as $connection) {
                $database = $this->app->make('db');
                $dbConnection = $database->connection($connection);

                // Commit the seed transaction
                $dbConnection->commit();

                // Start a new transaction for this test's data
                $dbConnection->beginTransaction();
            }

            self::$seeded = true;
        }

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
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

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

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    protected function getOrMakeUser(): Account
    {
        if ($this->user) {
            return $this->user;
        }

        $this->user = Account::factory()->withQualification()->createQuietly();
        DB::table('mship_account_role')->insert(['model_type' => Account::class, 'model_id' => $this->user->id, 'role_id' => Role::findByName('member')->id]); // Done manually to avoid firing events

        return $this->user;
    }

    protected function getOrMakePrivaccUser(): Account
    {
        if ($this->privacc) {
            return $this->privacc;
        }

        $user = Account::factory()->withQualification()->createQuietly();
        $role = Role::findByName('privacc');
        $role->givePermissionTo('*');
        DB::table('mship_account_role')->insert(['model_type' => Account::class, 'model_id' => $user->id, 'role_id' => $role->id]); // Done manually to avoid firing events

        return $this->privacc = $user->fresh();
    }
}
