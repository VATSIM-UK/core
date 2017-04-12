<?php

namespace App\Modules\Visittransfer\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Visittransfer\Models\Reference;
use App\Modules\Visittransfer\Events\ApplicationSubmitted;
use App\Modules\Visittransfer\Notifications\ApplicationReferenceRequest;

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

        $contactAt = \Carbon\Carbon::now();
        $remindAt = \Carbon\Carbon::now()->addDays(7);

        foreach ($refereesToBeNotified as $reference) {
            $reference->generateToken();

            $reference->status = Reference::STATUS_REQUESTED;
            $reference->contacted_at = $contactAt;
            $reference->reminded_at = $remindAt;
            $reference->save();

            $reference->notify(new ApplicationReferenceRequest($reference));
            $reference->notify((new ApplicationReferenceRequest($reference))->delay($remindAt));
        }
    }
}
