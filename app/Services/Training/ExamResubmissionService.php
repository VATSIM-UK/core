<?php

namespace App\Services\Training;

use App\Enums\ExamResultEnum;
use App\Models\Cts\ExamBooking;
use App\Models\Training\TrainingPosition\TrainingPosition;

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

        if ($examBooking->exam === 'OBS') {
            $trainingPosition = TrainingPosition::whereJsonContains('cts_positions', $examBooking->position_1)->firstOrFail();
            $service->forwardForObsExam($student, $trainingPosition);
        } else {
            $trainingPosition = TrainingPosition::whereHas('position', fn ($q) => $q
                ->where('callsign', $examBooking->position_1))
                ->firstOrFail();
            $service->forwardForExam($student, $trainingPosition, $userId);
        }
    }
}
