<?php

namespace App\Console\Commands\Atc;

use App\Models\Mship\Account;
use App\Notifications\Atc\TGNCInterest as InterestCheck;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class TGNCInterest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tgnc:interest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email TGNC home members to ensure that they are still interested in training.';

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
     * @return void
     */
    public function handle()
    {
        $this->info('Getting users from CTS...');
        $users = collect(TGNCInterestCts::getUsers());

        $this->info('Checking users exist within Core...');
        $users = $users->map(function ($item) {
            return Account::findOrRetrieve($item);
        })->reject(function ($value) {
            return $value === null;
        });

        $this->info("Sending {$users->count()} emails...");
        Notification::send($users, new InterestCheck());

        $this->line("Completed. {$users->count()} user(s) were processed.");
    }
}
