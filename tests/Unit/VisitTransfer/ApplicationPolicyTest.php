<?php

namespace Tests\Unit\VisitTransfer;

use App\Enums\VTCheckStatus;
use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ApplicationPolicyTest extends TestCase
{
    use DatabaseTransactions;

    protected Account $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = Account::factory()->create();

        $this->user->givePermissionTo(['vt.application.view.*', 'vt.application.accept.*', 'vt.application.reject.*', 'vt.application.complete.*']);
    }

    public static function providerApplicationState(): array
    {
        // Application Status, Checks Met, Can Accept, Can Reject, Can Complete, Can Cancel
        return [
            [Application::STATUS_CANCELLED, VTCheckStatus::Failed, false, false, false],
            [Application::STATUS_IN_PROGRESS, VTCheckStatus::Failed, false, true, false],
            [Application::STATUS_SUBMITTED, VTCheckStatus::Passed, true, true, false],
            [Application::STATUS_SUBMITTED, VTCheckStatus::Passed, true, true, false],
            [Application::STATUS_SUBMITTED, VTCheckStatus::Passed, true, true, false],
            [Application::STATUS_UNDER_REVIEW, VTCheckStatus::Passed, true, true, false],
            [Application::STATUS_ACCEPTED, VTCheckStatus::Passed, false, false, true],
        ];
    }

    #[DataProvider('providerApplicationState')]
    public function test_actions_policy($status, $checks_met, $can_accept, $can_reject, $can_complete)
    {
        $application = Application::factory()->create([
            'status' => $status,
            'check_outcome_90_day' => $checks_met,
            'check_outcome_50_hours' => $checks_met,
        ]);

        $policy = app(\App\Policies\VisitTransfer\ApplicationPolicy::class);

        $this->assertEquals($can_accept, $policy->accept($this->user, $application));
        $this->assertEquals($can_reject, $policy->reject($this->user, $application));
        $this->assertEquals($can_complete, $policy->complete($this->user, $application));
    }
}
