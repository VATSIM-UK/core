<?php

namespace App\Console\Commands\Training;

use App\Jobs\Training\CheckHomeMemberInWaitingList;
use App\Models\Training\WaitingList;
use Illuminate\Console\Command;

class CheckHomeMembersInWaitingLists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'training:check-home-members';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check that only home members are included in waiting lists.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // only check ATC lists
        $waitingLists = WaitingList::where(['department' => WaitingList::ATC_DEPARTMENT])->get()->load('accounts');

        // for each ATC list, check for home members
        $waitingLists->each(function ($waitingList) {
            $waitingList->accounts->each(function ($account) use ($waitingList) {
                CheckHomeMemberInWaitingList::dispatch($waitingList, $account);
            });
        });
    }
}
