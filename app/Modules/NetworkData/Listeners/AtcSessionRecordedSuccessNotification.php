<?php

namespace App\Modules\NetworkData\Listeners;

use App\Models\Mship\Account;
use App\Modules\NetworkData\Events\AtcSessionEnded;
use App\Modules\NetworkData\Notifications\AtcSessionRecordedConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;

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
