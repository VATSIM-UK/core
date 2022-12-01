<?php

namespace App\Console\Commands\WaitingLists;

use App\Console\Commands\Command;
use App\Models\Training\WaitingList;
use App\Notifications\Training\WaitingListAtcTopTen;
use Carbon\Carbon;

class SendAtcTopTenNotification extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'waitinglists:sendatctoptennotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends the top 10 members of an ATC waiting list a notification';

    /**
     * Executes all necessary console commands.
     */
    public function handle()
    {
        $waitingLists = WaitingList::all()->filter(function ($waitingList) {
            return $waitingList->isAtcList();
        });

        foreach ($waitingLists as $waitingList) {
            $nonNotifiedTopTenAccounts = $waitingList->accountsByEligibility(true)->take(10)->filter(function ($account) {
                return is_null($account->pivot->within_top_ten_notification_sent_at);
            });

            foreach ($nonNotifiedTopTenAccounts as $account) {
                $account->notify(new WaitingListAtcTopTen($waitingList->name));
                $account->pivot->within_top_ten_notification_sent_at = Carbon::now();
                $account->pivot->save();
            }
        }
    }
}
