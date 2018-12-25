<?php

namespace App\Listeners\VisitTransfer;

use App\Events\VisitTransfer\ApplicationUnderReview;
use App\Models\Mship\Account;
use App\Notifications\ApplicationReview;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyCommunityOfUnderReviewApplication implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ApplicationUnderReview $event)
    {
        // Disabled pending better implementation
        // $account = Account::find(1002707);
        // $account->notify(new ApplicationReview($event->application));
    }
}
