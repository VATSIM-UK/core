<?php

declare(strict_types=1);

namespace App\Listeners\Training\Exams;

use App\Events\Training\Exams\ExamAccepted;
use App\Models\Atc\Position;
use App\Models\Booking;
use App\Services\BookingService;
use Carbon\Carbon;

class CreateCtsBookingEntry
{
    public function handle(ExamAccepted $event): void
    {
        $examBooking = $event->examBooking;

        $position = Position::where('callsign', $examBooking->position_1)->first();

        app(BookingService::class)->create([
            'position_id' => $position?->id,
            'member_id' => $examBooking->student_id,
            'type' => Booking::TYPE_EXAM,
            'starts_at' => Carbon::parse($examBooking->taken_date.' '.$examBooking->taken_from),
            'ends_at' => Carbon::parse($examBooking->taken_date.' '.$examBooking->taken_to),
            'bookable_type' => $examBooking::class,
            'bookable_id' => $examBooking->id,
        ]);
    }
}
