<?php

namespace Tests\Feature\Database;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\ParallelTesting;
use Illuminate\Testing\ParallelTestingServiceProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\CreatesApplication;

class ParallelTestDatabaseTest extends TestCase
{
    use CreatesApplication;

    #[Test]
    public function a_recreated_worker_database_is_migrated_before_seeding_even_when_the_schema_flag_is_stale(): void
    {
        $this->withEmptyWorkerDatabase(function () {
            $this->runDatabaseSetupCallbacks();

            $this->assertTrue(DB::getSchemaBuilder()->hasTable('mship_role'));
            $this->assertTrue(DB::table('mship_role')->where('name', 'privacc')->exists());
            $this->assertTrue(DB::getSchemaBuilder()->hasTable('vt_application'));
            $this->assertGreaterThan(0, DB::table('migrations')->count());
        });
    }

    #[Test]
    public function a_failed_worker_migration_stops_before_database_seeding(): void
    {
        $this->withEmptyWorkerDatabase(function () {
            $seedStarted = false;
            Event::listen(CommandStarting::class, function ($event) use (&$seedStarted) {
                if ($event->command === 'db:seed') {
                    $seedStarted = true;
                }
            });
            Event::listen(MigrationsStarted::class, function () {
                throw new \RuntimeException('Worker migration failed');
            });

            try {
                $this->runDatabaseSetupCallbacks();
                $this->fail('A failed worker migration must stop database setup.');
            } catch (\RuntimeException $exception) {
                $this->assertSame('Worker migration failed', $exception->getMessage());
            }

            $this->assertFalse($seedStarted);
        });
    }

    private function runDatabaseSetupCallbacks(): void
    {
        $parallel = $this->app->make(ParallelTesting::class);
        $serverKeys = ['LARAVEL_PARALLEL_TESTING', 'TEST_TOKEN'];
        $originalServer = array_intersect_key($_SERVER, array_flip($serverKeys));
        $_SERVER['LARAVEL_PARALLEL_TESTING'] = '1';
        $_SERVER['TEST_TOKEN'] = 'recovery';

        try {
            $parallel->callSetUpTestDatabaseCallbacks(DB::getDatabaseName());
        } finally {
            foreach ($serverKeys as $key) {
                if (array_key_exists($key, $originalServer)) {
                    $_SERVER[$key] = $originalServer[$key];
                } else {
                    unset($_SERVER[$key]);
                }
            }
        }
    }

    private function withEmptyWorkerDatabase(callable $test): void
    {
        $originalConfig = config('database.connections.mysql');
        $originalDefault = config('database.default');
        $mysql = DB::connection('mysql');
        $resolvedConfig = $mysql->getConfig();
        $suffix = '_parallel_callback_'.getmypid().'_'.bin2hex(random_bytes(3));
        $database = substr($resolvedConfig['database'], 0, 64 - strlen($suffix)).$suffix;
        $temporaryConfig = array_replace($resolvedConfig, ['url' => null, 'database' => $database]);
        $schemaFlag = new \ReflectionProperty(ParallelTestingServiceProvider::class, 'schemaIsUpToDate');
        $originalFlag = $schemaFlag->getValue();
        $admin = $mysql->getSchemaBuilder();
        $admin->createDatabase($database);

        try {
            config(['database.default' => 'mysql', 'database.connections.mysql' => $temporaryConfig]);
            DB::purge('mysql');
            $schemaFlag->setValue(null, true);
            $this->assertFalse(DB::getSchemaBuilder()->hasTable('mship_role'));

            $test();
        } finally {
            DB::purge('mysql');
            config(['database.default' => $originalDefault, 'database.connections.mysql' => $originalConfig]);
            $schemaFlag->setValue(null, $originalFlag);
            $admin->dropDatabaseIfExists($database);
        }
    }
}
