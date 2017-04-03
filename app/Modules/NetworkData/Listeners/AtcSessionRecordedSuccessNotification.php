<?php

namespace App\Modules\NetworkData\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\NetworkData\Events\AtcSessionEnded;
use App\Modules\NetworkData\Notifications\AtcSessionRecordedConfirmation;

class AtcSessionRecordedSuccessNotification implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(AtcSessionEnded $event)
    {
        $user = $event->atcSession->account;

        $notification = new AtcSessionRecordedConfirmation($event->atcSession);

        $user->notify($notification);
    }
}
