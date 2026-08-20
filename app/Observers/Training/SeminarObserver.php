<?php

namespace App\Observers\Training;

use App\Models\Training\Seminar\Seminar;
use App\Services\Training\SeminarCtsSyncService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class SeminarObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Seminar $seminar): void
    {
        app(SeminarCtsSyncService::class)->syncSeminar($seminar);
    }

    public function updated(Seminar $seminar): void
    {
        app(SeminarCtsSyncService::class)->syncSeminar($seminar);
    }
}
