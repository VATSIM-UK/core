<?php

namespace App\Console\Commands\WaitingLists;

use App\Console\Commands\Command;
use App\Models\Training\WaitingList;
use App\Notifications\Training\WaitingListAtcTopTen;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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
        WaitingList::atc()->get()->mapWithKeys(function (WaitingList $waitingList) {
            return [$waitingList->name => $waitingList
                    ->accountsByEligibility(true)
                    ->take(10)
                    ->filter(function ($account) {
                        return is_null($account->pivot->within_top_ten_notification_sent_at);
                    }),
            ];
        })->each(function (Collection $accounts, string $waitingListName) {
            $accounts->each(function ($account) use ($waitingListName) {
                $account->notify(new WaitingListAtcTopTen($waitingListName));
                $account->pivot->within_top_ten_notification_sent_at = Carbon::now();
                $account->pivot->save();
            });
        });
    }
}
