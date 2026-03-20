<?php

namespace App\Services\Training;

use App\Enums\ExamResultEnum;
use App\Models\Cts\ExamBooking;
use App\Models\Training\TrainingPosition\TrainingPosition;
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
        $position = $this->resolveTrainingPosition($examBooking);

        if ($decision->isObservationExam) {
            $this->examForwardingService->forwardForObsExam($student, $position);

            return;
        }

        $this->examForwardingService->forwardForExam($student, $position, $userId);
    }

    private function resolveTrainingPosition(ExamBooking $examBooking): TrainingPosition
    {
        if ($examBooking->exam === 'OBS') {
            return TrainingPosition::whereJsonContains('cts_positions', $examBooking->position_1)->firstOrFail();
        }

        return TrainingPosition::whereHas('position', fn ($q) => $q
            ->where('callsign', $examBooking->position_1))
            ->firstOrFail();
    }

    public function getResubmissionDecision(ExamBooking $examBooking, string $result): ExamResubmissionDecision
    {
        if ($result !== ExamResultEnum::Incomplete->value) {
            return ExamResubmissionDecision::skip();
        }

        return ExamResubmissionDecision::forExamType($examBooking->exam === 'OBS');
    }
}
