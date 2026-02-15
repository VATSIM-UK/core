<?php

namespace Tests\Unit\VisitTransfer;

use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Reference;
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
        // Application Status, Number of Accepted References, Number of Pending References, Checks Met, Can Accept, Can Reject, Can Complete, Can Cancel
        return [
            [Application::STATUS_CANCELLED, 0, 2, false, false, false, false],
            [Application::STATUS_IN_PROGRESS, 0, 2, false, false, true, false],
            [Application::STATUS_SUBMITTED, 0, 2, true, true, true, false],
            [Application::STATUS_SUBMITTED, 2, 0, true, true, true, false],
            [Application::STATUS_SUBMITTED, 2, 2, true, true, true, false],
            [Application::STATUS_UNDER_REVIEW, 2, 0, true, true, true, false],
            [Application::STATUS_ACCEPTED, 2, 0, true, false, false, true],
        ];
    }

    #[DataProvider('providerApplicationState')]
    public function test_actions_policy($status, $num_accepted_references, $num_pending_references, $checks_met, $can_accept, $can_reject, $can_complete)
    {
        $application = Application::factory()->create([
            'status' => $status,
            'check_outcome_90_day' => $checks_met,
            'check_outcome_50_hours' => $checks_met,
        ]);

        Reference::factory()->count($num_accepted_references)->create(['application_id' => $application, 'status' => Reference::STATUS_ACCEPTED]);
        Reference::factory()->count($num_pending_references)->create(['application_id' => $application, 'status' => Reference::STATUS_REQUESTED]);

        $policy = app(\App\Policies\VisitTransfer\ApplicationPolicy::class);

        $this->assertEquals($can_accept, $policy->accept($this->user, $application));
        $this->assertEquals($can_reject, $policy->reject($this->user, $application));
        $this->assertEquals($can_complete, $policy->complete($this->user, $application));
    }
}
