<?php

namespace App\Listeners\Mship;

use App\Events\Mship\Qualifications\QualificationAdded;
use App\Models\Roster;
use App\Repositories\Cts\ExamResultRepository;
use Illuminate\Support\Facades\Log;

class AddNewlyQualifiedS1ToRoster
{
    public function __construct(private ExamResultRepository $examResultRepository)
    {
    }

    /**
     * If the member has recently passed the OBS exam, add them to the roster.
     * It is important to check for the recent exam as this event will fire for any member logging
     * in to VATSIM UK systems as an S1 and they are not necessarily a new S1.
     */
    public function handle(QualificationAdded $event): void
    {
        $newQualification = $event->qualification;

        if ($newQualification->code !== 'S1') {
            return;
        }

        $hasPassedExam = $this->examResultRepository->getPassedExamResultsOfTypeForMember(
            $event->account->id,
            'OBS'
        );

        // If the member has passed the OBS exam in the last month, add them to the roster.
        $hasRecentS1Exam = $hasPassedExam && $hasPassedExam->date->diffInMonths(now()) < 1;

        if ($hasRecentS1Exam) {
            Log::info("Adding {$event->account->id} to the roster as newly qualified S1");
            Roster::create(['account_id' => $event->account->id]);
        }
    }
}
