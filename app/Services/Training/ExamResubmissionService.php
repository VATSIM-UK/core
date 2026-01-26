<?php

namespace App\Services\Training;

use App\Enums\ExamResultEnum;
use App\Models\Atc\Position;
use App\Models\Cts\ExamBooking;

class ExamResubmissionService
{
    // Handles resubmitting a member for an exam if they recieve an incomplete result
    public function handle(ExamBooking $examBooking, string $result, int $userId): void
    {
        if ($result !== ExamResultEnum::Incomplete->value) {
            return;
        }

        $service = new ExamForwardingService;

        $student = $examBooking->student;
        $position = Position::query()
            ->where('callsign', $examBooking->position_1)
            ->first();

        if ($examBooking->exam === 'OBS') {
            $service->forwardForObsExam($student, $position);
        } else {
            $service->forwardForExam($student, $position, $userId);
        }
    }
}
