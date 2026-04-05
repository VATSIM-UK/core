<?php

namespace App\Listeners\Training\Exams;

use App\Events\Training\Exams\PracticalExamCompleted;
use App\Services\Training\TrainingSuccessesAnnouncementService;

class NotifyDiscordPracticalExamSuccess
{
    public function __construct(private TrainingSuccessesAnnouncementService $trainingSuccessesAnnouncementService) {}

    /**
     * Handle the event.
     */
    public function handle(PracticalExamCompleted $event): void
    {
        $studentAccount = $event->examBooking->studentAccount();
        $practicalResult = $event->practicalResult;
        $examBooking = $event->examBooking;

        if ($practicalResult->isPassed() && $studentAccount) {
            $this->trainingSuccessesAnnouncementService->announceExamPassed($examBooking);
        }
    }
}
