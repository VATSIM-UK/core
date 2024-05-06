<?php

namespace App\Listeners\Training\Endorsement;

use App\Events\Training\EndorsementRequestCreated as EndorsementRequestCreatedEvent;
use App\Models\Mship\Account;
use App\Notifications\Mship\Endorsement\EndorsementRequestCreated as EndorsementRequestCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class NotifyEndorsementRequestCreated implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(EndorsementRequestCreatedEvent $event): void
    {
        $accountsToNotify = Account::permission('endorsement-request.approve.*')->get();

        $accountsToNotify->each(fn(Account $account) => $account->notify(new EndorsementRequestCreatedNotification($event->getEndorsementRequest())));
    }
}
