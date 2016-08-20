<?php namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Events\ApplicationSubmitted;
use App\Modules\Visittransfer\Jobs\SendReferenceRequestEmail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class InitialContactOfAllReferees implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ApplicationSubmitted $event)
    {
        foreach($event->application->referees as $reference){
            $reference->generateToken();

            $referenceRequestEmailJob = new SendReferenceRequestEmail($reference);

            dispatch($referenceRequestEmailJob->onQueue("low"));

        }
    }
}