<?php

namespace App\Services\Training;

use App\Enums\ExamResultEnum;
use App\Models\Atc\Position;
use App\Models\Cts\ExamBooking;
use App\Services\Training\DTO\ExamResubmissionDecision;

class ExamResubmissionService
{
    public function __construct(private readonly ExamForwardingService $examForwardingService) {}

    // Handles resubmitting a member for an exam if they recieve an incomplete result
    public function handle(ExamBooking $examBooking, string $result, int $userId): void
    {
        $decision = $this->getResubmissionDecision($examBooking, $result);

        if (! $decision->shouldResubmit) {
            return;
        }

        $student = $examBooking->student;
        $position = Position::query()
            ->where('callsign', $examBooking->position_1)
            ->first();

        if ($decision->isObservationExam) {
            $this->examForwardingService->forwardForObsExam($student, $position);

            return;
        }

        $this->examForwardingService->forwardForExam($student, $position, $userId);
    }

    public function getResubmissionDecision(ExamBooking $examBooking, string $result): ExamResubmissionDecision
    {
        if ($result !== ExamResultEnum::Incomplete->value) {
            return ExamResubmissionDecision::skip();
        }

        return ExamResubmissionDecision::forExamType($examBooking->exam === 'OBS');
    }
}
