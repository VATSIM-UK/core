<?php

namespace App\Listeners\Training\Exams;

use App\Events\Training\Exams\ExamAccepted;
use App\Notifications\Training\Exams\ExamAcceptedStudentNotification;

class NotifyStudentExamAccepted
{
    /**
     * Handle the event.
     */
    public function handle(ExamAccepted $event): void
    {
        $student = $event->examBooking->student;

        // Get the student's account from the core system
        $studentAccount = $student->account;

        $studentAccount->notify(new ExamAcceptedStudentNotification($event->examBooking));
    }
}
