<?php

namespace App\Console\Commands\Deployment;

use App\Models\Mship\Account;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class HerokuPostDeploy extends Command
{
    protected $signature = 'postdeploy:heroku';

    protected $description = 'Run post-deploy on Heroku';

    public function handle()
    {
        $this->runMigrationsFor(app()->environment());

        if (preg_match('/DEMO/', config('vatsim-sso.key'))) {
            $this->grantSuperman(Account::findOrRetrieve(1300001));
        }
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
                $this->call('cts:migrate:fresh');
                break;
        }
        $this->info('=====================');
        $this->info('Success!');
        $this->info('=====================');
    }

    public function grantSuperman(Account $account)
    {
        $account->assignRole(Role::findByName('privacc'));
    }
}
