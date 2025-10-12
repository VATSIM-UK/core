<?php

namespace App\Listeners\Training\Exams;

use App\Events\Training\Exams\ExamAccepted;
use App\Models\Cts\Booking;

class CreateCtsBookingEntry
{
    /**
     * Handle the event.
     */
    public function handle(ExamAccepted $event): void
    {
        $examBooking = $event->examBooking;

        // Create a CTS booking entry for the exam
        Booking::create([
            'date' => $examBooking->taken_date,
            'from' => $examBooking->taken_from,
            'to' => $examBooking->taken_to,
            'position' => $examBooking->position_1,
            'member_id' => $examBooking->student_id,
            'type' => 'EX', // Exam type
            'type_id' => $event->examBooking->id,
            'time_booked' => now(),
            'local_id' => 0,
            'groupID' => null,
            'eurobook_id' => null,
            'eurobook_import' => 0,
        ]);
    }
}
