<?php

namespace App\Console\Commands\Deployment;

use Illuminate\Console\Command;
use Tests\Database\MockCtsDatabase;

class HerokuPostDeploy extends Command
{
    protected $signature = 'postdeploy:heroku';
    protected $description = 'Run post-deploy on Heroku';

    public function handle()
    {
        $this->runMigrationsFor(app()->environment());
    }

    public function runMigrationsFor($environment)
    {
        switch ($environment) {
            case 'production':
                $this->call('migrate', ['--force' => true]);
                break;
            case 'staging':
                $this->call('migrate');
                break;
            case 'development':
                $this->call('nova:install');
                $this->call('migrate:fresh');
                MockCtsDatabase::destroy();
                MockCtsDatabase::create();
                break;
        }
    }
}
