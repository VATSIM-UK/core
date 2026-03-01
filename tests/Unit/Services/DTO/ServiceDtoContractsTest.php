<?php

namespace Tests\Unit\Services\DTO;

use App\Services\Mship\DTO\ManagementRedirectResult;
use App\Services\Mship\DTO\ManagementViewResult;
use App\Services\Training\DTO\ExamResubmissionDecision;
use App\Services\Training\DTO\WaitingListFlagSummaryResult;
use App\Services\Training\DTO\WaitingListSelfEnrolmentEligibility;
use App\Services\VisitTransfer\DTO\ApplicationActionResult;
use Tests\TestCase;

class ServiceDtoContractsTest extends TestCase
{
    public function test_management_redirect_result_detects_flash_messages(): void
    {
        $result = new ManagementRedirectResult('mship.manage.dashboard', 'success', 'Saved');

        $this->assertTrue($result->hasFlashMessage());

        $withoutMessage = new ManagementRedirectResult('mship.manage.dashboard');

        $this->assertFalse($withoutMessage->hasFlashMessage());
    }

    public function test_management_view_result_detects_flash_messages(): void
    {
        $result = new ManagementViewResult(true, 'mship.manage.dashboard', [], 'error', 'Nope');

        $this->assertTrue($result->hasFlashMessage());

        $withoutMessage = new ManagementViewResult(false, 'mship.manage.dashboard');

        $this->assertFalse($withoutMessage->hasFlashMessage());
    }

    public function test_waiting_list_self_enrolment_eligibility_named_constructors(): void
    {
        $allowed = WaitingListSelfEnrolmentEligibility::allow();
        $denied = WaitingListSelfEnrolmentEligibility::deny('Not eligible');

        $this->assertTrue($allowed->allowed);
        $this->assertNull($allowed->reason);

        $this->assertFalse($denied->allowed);
        $this->assertSame('Not eligible', $denied->reason);
    }

    public function test_waiting_list_flag_summary_result_serialises_consistently(): void
    {
        $result = new WaitingListFlagSummaryResult(['total' => 3, 'active' => 2]);

        $this->assertSame([
            'summary' => ['total' => 3, 'active' => 2],
        ], $result->toArray());
    }

    public function test_exam_resubmission_decision_named_constructors(): void
    {
        $skip = ExamResubmissionDecision::skip();
        $obs = ExamResubmissionDecision::forExamType(true);

        $this->assertFalse($skip->shouldResubmit);
        $this->assertFalse($skip->isObservationExam);
        $this->assertTrue($obs->shouldResubmit);
        $this->assertTrue($obs->isObservationExam);
    }

    public function test_application_action_result_defaults_are_preserved(): void
    {
        $result = new ApplicationActionResult(false, 'visiting.application.continue', ['abc123']);

        $this->assertFalse($result->useBackRedirect);
        $this->assertSame('visiting.application.continue', $result->route);
        $this->assertSame(['abc123'], $result->routeParameters);
        $this->assertSame('success', $result->level);
        $this->assertNull($result->message);
        $this->assertFalse($result->withInput);
    }
}
