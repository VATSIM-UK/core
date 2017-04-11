<?php

namespace App\Modules\Visittransfer\Listeners;

use App\Modules\Visittransfer\Models\Reference;
use App\Modules\Visittransfer\Notifications\ApplicationReferenceRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Events\ApplicationSubmitted;
use App\Modules\Visittransfer\Jobs\SendInitialRefereeRequestEmail;
use App\Modules\Visittransfer\Jobs\SendRefereeRequestReminderEmail;

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

            $reference->notify(new ApplicationReferenceRequest($reference));

            $reference->status = Reference::STATUS_REQUESTED;
            $reference->contacted_at = \Carbon\Carbon::now();
            $reference->save();

            $referenceRequestReminderEmailJob = new SendRefereeRequestReminderEmail($reference);
            $delayPeriod                      = \Carbon\Carbon::now()->diffInSeconds(\Carbon\Carbon::now()->addDays(7));
            dispatch($referenceRequestReminderEmailJob->delay($delayPeriod)->onQueue('low'));
        }
    }
}
