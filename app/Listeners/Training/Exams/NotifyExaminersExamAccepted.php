<?php

namespace App\Listeners\Training\Exams;

use App\Events\Training\Exams\ExamAccepted;
use App\Notifications\Training\Exams\ExamAcceptedExaminerNotification;

class NotifyExaminersExamAccepted
{
    /**
     * Handle the event.
     */
    public function handle(ExamAccepted $event): void
    {
        $examiners = $event->examBooking->examiners;

        // Notify primary examiner
        $examiners->primaryExaminer->account->notify(
            new ExamAcceptedExaminerNotification($event->examBooking)
        );

        // Notify secondary examiner if assigned
        if ($examiners->secondaryExaminer) {
            $examiners->secondaryExaminer->account->notify(
                new ExamAcceptedExaminerNotification($event->examBooking)
            );
        }

        // Notify trainee examiner if assigned
        if ($examiners->traineeExaminer) {
            $examiners->traineeExaminer->account->notify(
                new ExamAcceptedExaminerNotification($event->examBooking)
            );
        }
    }
}
