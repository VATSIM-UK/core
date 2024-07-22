<?php

namespace App\Listeners\VisitTransferLegacy;

use App\Events\VisitTransferLegacy\ApplicationSubmitted;
use App\Models\VisitTransferLegacy\Reference;
use App\Notifications\ApplicationReferenceRequest;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAllReferees implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
        //
    }

    public function handle(ApplicationSubmitted $event)
    {
        $refereesToBeNotified = $event->application->referees->filter(function ($ref) {
            if ($ref->reminded_at == null) {
                return ! $ref->is_submitted;
            }

            return $ref->reminded_at->isPast() && ! $ref->is_submitted;
        });

        $contactAt = Carbon::now();
        $delay = 7; // In days
        $remindAt = Carbon::now()->addDays($delay);

        $draftReferences = $event->application->referees()->draft()->count();

        if ($draftReferences > 0) {
            // This is the first time the job has run
            $this->release($delay * 24 * 60 * 60);
        }

        foreach ($refereesToBeNotified as $reference) {
            if ($reference->status == Reference::STATUS_DRAFT) {
                // This a a new reference
                $reference->generateToken();
                $reference->status = Reference::STATUS_REQUESTED;
                $reference->contacted_at = $contactAt;
                $reference->reminded_at = $remindAt;
                $reference->save();
            }

            $reference->notify(new ApplicationReferenceRequest($reference));
        }
    }
}
