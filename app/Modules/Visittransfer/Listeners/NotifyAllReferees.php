<?php namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Events\ApplicationSubmitted;
use App\Modules\Visittransfer\Jobs\SendInitialRefereeRequestEmail;
use App\Modules\Visittransfer\Jobs\SendRefereeRequestReminderEmail;
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
        $refereesToBeNotified = $event->application->referees->filter(function ($ref) {
            return !$ref->is_requested && !$ref->is_submitted;
        });

        foreach ($refereesToBeNotified as $reference) {
            $reference->generateToken();

            $referenceRequestEmailJob = new SendInitialRefereeRequestEmail($reference);
            dispatch($referenceRequestEmailJob->onQueue("low"));

            $referenceRequestReminderEmailJob = new SendRefereeRequestReminderEmail($reference);
            $delayPeriod = \Carbon\Carbon::now()->diffInSeconds(\Carbon\Carbon::now()->addDays(7));
            dispatch($referenceRequestReminderEmailJob->delay($delayPeriod)->onQueue("low"));
        }
    }
}
