<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\WaitingListAtcPositionsChanged;
use App\Models\Mship\Account;
use App\Notifications\Training\WaitingListAtcTopTen;

class SendTopTenAtcNotifications
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(WaitingListAtcPositionsChanged $event)
    {
        $matched = 0;
        $topTenNonNotified = $event->waitingList->accounts()->get()->filter(function (Account $account) use (&$matched, $event) {
            if ($matched >= 10) { // After we have 10 results, retrieving account positions is unnecessary resource usage
                return false;
            }
            if ($event->waitingList->accountPosition($account) <= 10) {
                $matched = $matched + 1;
                if ($account->pivot->top_ten_notified == 'no') { // We do not want to notify people who have already been notified of their top 10 position
                    return true;
                }

                return false;
            }

            return false;
        });

        $topTenNonNotified->each(function (Account $account) use ($event) {
            $account->notify(new WaitingListAtcTopTen($event->waitingList->name));
            $account->pivot->top_ten_notified = 'yes';
            $account->pivot->save();
        });
    }
}
