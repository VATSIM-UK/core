<?php

namespace App\Listeners\Training\Exams;

use App\Events\Training\Exams\PracticalExamCompleted;
use App\Notifications\Training\Exams\PracticalExamResultNotification;

class NotifyStudentPracticalExamCompleted
{
    /**
     * Handle the event.
     */
    public function handle(PracticalExamCompleted $event): void
    {
        $student = $event->practicalResult->student;

        // Get the student's account from the core system
        $studentAccount = $student->account;

        $studentAccount->notify(new PracticalExamResultNotification($event->practicalResult));
    }
}
