<?php

namespace App\Listeners\VisitTransfer;

use App\Models\Mship\Account;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\VisitTransfer\ApplicationUnderReview;
use App\Notifications\ApplicationReview;

class NotifyCommunityOfUnderReviewApplication implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ApplicationUnderReview $event)
    {
        // TODO: Use the staff services feature to choose recipient
        $account = Account::find(1002707);
        $account->notify(new ApplicationReview($event->application));
    }
}
