<?php namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Events\ApplicationStatusChanged;
use App\Modules\Visittransfer\Events\ApplicationUnderReview;
use App\Modules\Visittransfer\Jobs\SendApplicantStatusChangeEmail;
use App\Modules\Visittransfer\Jobs\SendCommunityApplicationReviewEmail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyCommunityOfUnderReviewApplication implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ApplicationUnderReview $event)
    {
        $confirmationEmailJob = new SendCommunityApplicationReviewEmail($event->application);

        dispatch($confirmationEmailJob->onQueue("low"));
    }
}
