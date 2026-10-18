<?php

namespace App\Observers\Training;

use App\Models\Training\Seminar\Seminar;
use App\Services\Training\SeminarCtsSyncService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class SeminarObserver implements ShouldHandleEventsAfterCommit
{
    private const CTS_RELEVANT_FIELDS = [
        'name',
        'date',
        'from',
        'to',
        'capacity',
        'created_by',
    ];

    public function created(Seminar $seminar): void
    {
        app(SeminarCtsSyncService::class)->syncSeminar($seminar);
    }

    public function updated(Seminar $seminar): void
    {
        if (! $seminar->wasChanged(self::CTS_RELEVANT_FIELDS)) {
            return;
        }

        app(SeminarCtsSyncService::class)->syncSeminar($seminar);
    }
}
