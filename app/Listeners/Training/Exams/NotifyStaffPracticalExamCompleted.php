<?php

namespace App\Listeners\Training\Exams;

use App\Events\Training\Exams\PracticalExamCompleted;
use App\Notifications\Training\Exams\PracticalExamCompletedNotification;
use Illuminate\Support\Facades\Notification;

class NotifyStaffPracticalExamCompleted
{
    /**
     * Handle the event.
     */
    public function handle(PracticalExamCompleted $event): void
    {
        // TODO: Future implementation should use an email group
        $staffToNotify = collect(
            'adam.arkley@vatsim.uk',
            'will.jennings@vatsim.uk'
        );

        foreach ($staffToNotify as $staff) {
            Notification::route('mail', $staff)
                ->notify(new PracticalExamCompletedNotification($event->practicalResult));
        }
    }
}
