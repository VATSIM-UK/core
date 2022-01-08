<?php

namespace Tests\Unit\VisitTransfer;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\NetworkData\Atc;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Reference;
use App\Notifications\ApplicationAccepted;
use Carbon\Carbon;
use Faker\Provider\Base;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ApplicationPolicyTest extends TestCase
{
    use DatabaseTransactions;


    public function providerApplicationState()
    {
        // Application Status, Number of Accepted References, Number of Pending References, Checks Met, Can Accept, Can Reject, Can Complete, Can Cancel
        return [
            [Application::STATUS_CANCELLED, 0, 2, false, false, false, false, false],
            [Application::STATUS_IN_PROGRESS, 0, 2, false, false, true, false, false],
            [Application::STATUS_SUBMITTED, 0, 2, true, true, true, false, false],
            [Application::STATUS_SUBMITTED, 2, 0, true, true, true, false, false],
            [Application::STATUS_SUBMITTED, 2, 2, true, true, true, false, false],
            [Application::STATUS_UNDER_REVIEW, 2, 0, true, true, true, false, false],
            [Application::STATUS_ACCEPTED, 2, 0, true, false, false, true, true],
        ];
    }


    /** 
     * @test
     * @dataProvider providerApplicationState
     */
    public function testActionsPolicy($status, $num_accepted_references, $num_pending_references, $checks_met, $can_accept, $can_reject, $can_complete, $can_cancel)
    {
        $application = factory(Application::class)->create([
            'status' => $status,
            'check_outcome_90_day' => $checks_met,
            'check_outcome_50_hours' => $checks_met,
        ]);


        factory(Reference::class, $num_accepted_references)->create(['application_id' => $application, "status" => Reference::STATUS_ACCEPTED]);
        factory(Reference::class, $num_pending_references)->create(['application_id' => $application, "status" => Reference::STATUS_REQUESTED]);

        $this->assertEquals($can_accept, $this->user->can('accept', $application));
        $this->assertEquals($can_reject, $this->user->can('reject', $application));
        $this->assertEquals($can_complete, $this->user->can('complete', $application));
    }
}
