<?php

namespace App\Console\Commands;

use App\Exceptions\Mship\InvalidCIDException;
use App\Models\Mship\Account;
use App\Models\Mship\Role;
use Illuminate\Console\Command;

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
        $supermanRole = Role::find(1);

        $accountID = $this->argument('cid');

        $account = null;

        try {
            $account = Account::findOrRetrieve($accountID);
        } catch (InvalidCIDException $exception) {
            $this->error('The specific CID was not found.');
        }

        if ($account->hasRole($supermanRole)) {
            $this->error('The specified account already has the "superman" role.');
        }

        $account->roles()->attach($supermanRole);

        $this->info('Account added to the superman role!');
    }
}
