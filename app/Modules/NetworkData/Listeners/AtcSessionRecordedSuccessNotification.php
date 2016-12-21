<?php

namespace App\Modules\NetworkData\Listeners;

use App\Models\Mship\Account;
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
        $user = Account::find(980234);

        $user->notify(new AtcSessionRecordedConfirmation($event->atcSession));
    }
}
