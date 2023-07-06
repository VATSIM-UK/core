<?php

namespace App\Console\Commands\Training;

use App\Jobs\Training\CheckAccountWaitingListEligibility;
use App\Models\Training\WaitingList;
use Illuminate\Console\Command;

class CheckWaitingListEligibilityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waiting-lists:check-eligibility {account?} {--waiting-list}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create jobs to check waiting list eligibility for all accounts on the waiting lists';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $activeWaitingLists = WaitingList::all();

        if ($this->argument('account')) {
            $activeWaitingLists = $activeWaitingLists->filter(function ($waitingList) {
                return $waitingList->accounts->contains($this->argument('account'));
            });

            if ($activeWaitingLists->isEmpty()) {
                $this->error('Account not found on any waiting lists');

                return Command::FAILURE;
            }
        }

        if ($this->option('waiting-list')) {
            $activeWaitingLists = $activeWaitingLists->filter(function ($waitingList) {
                return $waitingList->id === $this->option('waiting-list');
            });

            if ($activeWaitingLists->isEmpty()) {
                $this->error('Waiting list not found');

                return Command::FAILURE;
            }
        }

        foreach ($activeWaitingLists as $waitingList) {
            foreach ($waitingList->accounts as $account) {
                CheckAccountWaitingListEligibility::dispatch($account);
            }
        }

        return Command::SUCCESS;
    }
}
