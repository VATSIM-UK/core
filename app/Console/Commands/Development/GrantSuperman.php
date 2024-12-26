<?php

namespace App\Console\Commands\Development;

use App\Models\Mship\Account;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Permission\Models\Role;

class GrantSuperman extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grant:superman {cid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grants the "superman" permission to the specified account.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $supermanRole = Role::findByName('privacc');

        $accountID = $this->argument('cid');

        $account = null;

        try {
            $account = Account::findOrFail($accountID);
        } catch (ModelNotFoundException $exception) {
            $this->error('The specific CID was not found.');

            return;
        }

        if ($account != null && $account->hasRole($supermanRole)) {
            $this->error('The specified account already has the "superman" role.');

            return;
        }

        $account->assignRole($supermanRole);

        $this->info('Account added to the superman role!');
    }
}
