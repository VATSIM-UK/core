<?php

namespace App\Listeners\NetworkData;

use App\Events\NetworkData\AtcSessionEnded;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\AtcSessionRecordedConfirmation;

class AtcSessionRecordedSuccessNotification implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(AtcSessionEnded $event)
    {
        $user = $event->atcSession->account;

        if (! empty($user->slack_id)) {
            $user->notify(new AtcSessionRecordedConfirmation($event->atcSession));
        }
    }
}
