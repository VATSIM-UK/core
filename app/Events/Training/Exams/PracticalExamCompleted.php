<?php

namespace App\Events\Training\Exams;

use App\Models\Cts\ExamBooking;
use App\Models\Cts\PracticalResult;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PracticalExamCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public ExamBooking $examBooking, public PracticalResult $practicalResult) {}
}
