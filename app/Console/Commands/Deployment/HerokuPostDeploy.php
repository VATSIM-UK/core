<?php

namespace App\Console\Deployment;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HerokuPostDeploy extends Command
{
    protected $signature = 'postdeploy:heroku';
    protected $description = 'Run post-deploy on Heroku';

    public function handle()
    {
        $this->runMigrationsFor(app()->environment());
        $this->clearResponseCache();
    }

    public function runMigrationsFor($environment)
    {
        if (!$this->checkDatabaseConnection()) {
            return false;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        switch ($environment) {
            case 'production':
                $this->call('migrate', ['--force' => true]);
                break;
            case 'staging':
                $this->call('migrate');
                break;
            case 'development':
                $this->call('migrate:fresh');
                break;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    private function checkDatabaseConnection()
    {
        try {
            DB::connection()->getDatabaseName();

            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function clearResponseCache()
    {
        if (!class_exists("\Spatie\ResponseCache\ResponseCacheServiceProvider")) {
            return false;
        }

        $this->call('responsecache:clear');
    }
}
