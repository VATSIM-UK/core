<?php

namespace Tests\Unit\VisitTransferLegacy;

use App\Models\VisitTransferLegacy\Application;
use App\Models\VisitTransferLegacy\Reference;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ApplicationPolicyTest extends TestCase
{
    use DatabaseTransactions;

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
    public function testActionsPolicy($status, $num_accepted_references, $num_pending_references, $checks_met, $can_accept, $can_reject, $can_complete)
    {
        $application = factory(Application::class)->create([
            'status' => $status,
            'check_outcome_90_day' => $checks_met,
            'check_outcome_50_hours' => $checks_met,
        ]);

        factory(Reference::class, $num_accepted_references)->create(['application_id' => $application, 'status' => Reference::STATUS_ACCEPTED]);
        factory(Reference::class, $num_pending_references)->create(['application_id' => $application, 'status' => Reference::STATUS_REQUESTED]);

        $this->assertEquals($can_accept, $this->user->can('accept', $application));
        $this->assertEquals($can_reject, $this->user->can('reject', $application));
        $this->assertEquals($can_complete, $this->user->can('complete', $application));
    }
}
