<?php namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Events\ReferenceDeleted;
use App\Modules\Visittransfer\Jobs\SendRefereeConfirmationEmail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyRefereeOnReferenceCompletion implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ReferenceDeleted $event)
    {
        $confirmationEmailJob = new SendRefereeConfirmationEmail($event->reference);

        dispatch($confirmationEmailJob->onQueue("low"));
    }
}
