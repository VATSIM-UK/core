<?php

namespace Tests\Unit\Training;

use App\Enums\ExamResultEnum;
use App\Models\Cts\ExamBooking;
use App\Services\Training\ExamForwardingService;
use App\Services\Training\ExamResubmissionService;
use Tests\TestCase;

class ExamResubmissionServiceTest extends TestCase
{
    public function test_it_skips_resubmission_for_non_incomplete_results(): void
    {
        $service = new ExamResubmissionService($this->mock(ExamForwardingService::class));
        $booking = new ExamBooking;
        $booking->exam = 'OBS';

        $decision = $service->getResubmissionDecision($booking, ExamResultEnum::Passed->value);

        $this->assertFalse($decision->shouldResubmit);
        $this->assertFalse($decision->isObservationExam);
    }

    public function test_it_marks_observation_exam_for_resubmission_when_incomplete(): void
    {
        $service = new ExamResubmissionService($this->mock(ExamForwardingService::class));
        $booking = new ExamBooking;
        $booking->exam = 'OBS';

        $decision = $service->getResubmissionDecision($booking, ExamResultEnum::Incomplete->value);

        $this->assertTrue($decision->shouldResubmit);
        $this->assertTrue($decision->isObservationExam);
    }

    public function test_it_marks_non_observation_exam_for_standard_resubmission_when_incomplete(): void
    {
        $service = new ExamResubmissionService($this->mock(ExamForwardingService::class));
        $booking = new ExamBooking;
        $booking->exam = 'TWR';

        $decision = $service->getResubmissionDecision($booking, ExamResultEnum::Incomplete->value);

        $this->assertTrue($decision->shouldResubmit);
        $this->assertFalse($decision->isObservationExam);
    }
}
