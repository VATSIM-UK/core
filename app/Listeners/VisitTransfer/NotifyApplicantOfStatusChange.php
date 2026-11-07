<?php

namespace App\Listeners\VisitTransfer;

use App\Events\VisitTransfer\ApplicationStatusChanged as ApplicationStatusChangedEvent;
use App\Events\VisitTransfer\ApplicationWithdrawn;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyApplicantOfStatusChange implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(ApplicationStatusChangedEvent $event)
    {
        $application = $event->application;
        if (! $application) {
            return;
        }

        $application->account?->notify(new \App\Notifications\ApplicationStatusChanged($application));
    }

    public function viaConnection(ApplicationStatusChangedEvent $event): ?string
    {
        // Capture withdrawal details before the application is deleted.
        return $event instanceof ApplicationWithdrawn ? 'sync' : null;
    }
}
