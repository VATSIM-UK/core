<?php

namespace App\Console\Commands;

use DB;
use Carbon\Carbon;
use App\Models\Statistic;
use App\Models\Mship\State;
use App\Models\Mship\Account;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class ApiGenerator extends Command
{
    /**
     * The console command signature.
     *
     * The name of the command, along with any expected arguments.
     *
     * @var string
     */
    protected $signature = 'api:generator {name : The name of the API account.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an account for the API.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $account = \App\Models\Api\Account::create([
            "name"     => $this->argument("name"),
            "api_token" => strtoupper(md5(microtime())),
        ]);

        $this->info($account->name . " :: " . $account->api_token);
    }
}
