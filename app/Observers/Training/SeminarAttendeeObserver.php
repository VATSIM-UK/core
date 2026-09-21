<?php

namespace App\Observers\Training;

use App\Models\Training\Seminar\SeminarAttendee;
use App\Services\Training\SeminarCtsSyncService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class SeminarAttendeeObserver implements ShouldHandleEventsAfterCommit
{
    public function created(SeminarAttendee $seminarAttendee): void
    {
        app(SeminarCtsSyncService::class)->syncAttendee($seminarAttendee);
    }
}
