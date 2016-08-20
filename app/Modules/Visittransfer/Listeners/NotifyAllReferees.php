<?php namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Events\ApplicationSubmitted;
use App\Modules\Visittransfer\Jobs\SendRefereeRequestEmail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAllReferees implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ApplicationSubmitted $event)
    {
        foreach($event->application->referees as $reference){
            $reference->generateToken();

            $referenceRequestEmailJob = new SendRefereeRequestEmail($reference);

            dispatch($referenceRequestEmailJob->onQueue("low"));

        }
    }
}