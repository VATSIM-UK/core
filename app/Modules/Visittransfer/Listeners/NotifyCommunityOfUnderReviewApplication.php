<?php

namespace App\Modules\Visittransfer\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Events\ApplicationUnderReview;
use App\Modules\Visittransfer\Jobs\SendCommunityApplicationReviewEmail;

class NotifyCommunityOfUnderReviewApplication implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ApplicationUnderReview $event)
    {
        $confirmationEmailJob = new SendCommunityApplicationReviewEmail($event->application);

        dispatch($confirmationEmailJob->onQueue('low'));
    }
}
