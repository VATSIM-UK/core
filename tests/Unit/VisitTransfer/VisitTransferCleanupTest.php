<?php

namespace Tests\Unit\VisitTransfer;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class VisitTransferCleanupTest extends TestCase
{
    use DatabaseTransactions;

    public $newApplication;

    public $oldApplication;

    public $facility;

    public function setUp(): void
    {
        parent::setUp();

        // A draft application that has just been started
        $this->facility = factory(\App\Models\VisitTransfer\Facility::class, 'atc_visit')->create([
            'stage_reference_enabled' => 1,
            'stage_reference_quantity' => 1,
        ]);
        $application = $this->user->createVisitingTransferApplication([
            'type' => \App\Models\VisitTransfer\Application::TYPE_VISIT,
            'facility_id' => $this->facility->id,
        ]);
        $this->newApplication = $application->fresh();

        // A draft application that was started 3 hours ago
        Carbon::setTestNow(Carbon::now()->subHours(3));
        $user = \App\Models\Mship\Account::factory()->create();
        $application = $user->createVisitingTransferApplication([
            'type' => \App\Models\VisitTransfer\Application::TYPE_VISIT,
            'facility_id' => $this->facility->id,
        ]);
        Carbon::setTestNow();
        $this->oldApplication = $application->fresh();
    }

    /** @test */
    public function itOnlyCancelsOldApplications()
    {
        Artisan::call('visit-transfer:cleanup');

        $this->assertEquals(\App\Models\VisitTransfer\Application::STATUS_EXPIRED, $this->oldApplication->fresh()->status);
        $this->assertEquals(\App\Models\VisitTransfer\Application::STATUS_IN_PROGRESS, $this->newApplication->fresh()->status);
    }

    /** @test */
    public function itLapsesApplicationsForOldContactedReferees()
    {
        Mail::fake();

        // A submitted application with a pending reference that has expired
        $application = factory(\App\Models\VisitTransfer\Application::class)->create([
            'type' => \App\Models\VisitTransfer\Application::TYPE_VISIT,
            'facility_id' => $this->facility->id,
            'status' => \App\Models\VisitTransfer\Application::STATUS_SUBMITTED,
            'references_required' => 1,
            'submitted_at' => Carbon::now(),
        ]);
        Carbon::setTestNow(Carbon::now()->subDays(15));
        factory(\App\Models\VisitTransfer\Reference::class)->create([
            'status' => \App\Models\VisitTransfer\Reference::STATUS_REQUESTED,
            'contacted_at' => Carbon::now(),
            'application_id' => $application->id,
        ]);
        Carbon::setTestNow();

        Artisan::call('visit-transfer:cleanup');
        $this->assertEquals(\App\Models\VisitTransfer\Application::STATUS_LAPSED, $application->fresh()->status);
    }

    /** @test */
    public function itWontIncorrectlyLapseApplications()
    {
        // A submitted application with a requested (contacted & pending) reference that is not old
        $application1 = factory(\App\Models\VisitTransfer\Application::class)->create([
            'type' => \App\Models\VisitTransfer\Application::TYPE_VISIT,
            'facility_id' => $this->facility->id,
            'status' => \App\Models\VisitTransfer\Application::STATUS_SUBMITTED,
            'references_required' => 1,
            'submitted_at' => Carbon::now(),
        ]);
        Carbon::setTestNow(Carbon::now()->subDays(12));
        factory(\App\Models\VisitTransfer\Reference::class)->create([
            'status' => \App\Models\VisitTransfer\Reference::STATUS_REQUESTED,
            'contacted_at' => Carbon::now(),
            'application_id' => $application1->id,
        ]);
        Carbon::setTestNow();

        // A submitted application with a requested (pending - not contacted) reference that is not old
        $application2 = factory(\App\Models\VisitTransfer\Application::class)->create([
            'type' => \App\Models\VisitTransfer\Application::TYPE_VISIT,
            'facility_id' => $this->facility->id,
            'status' => \App\Models\VisitTransfer\Application::STATUS_SUBMITTED,
            'references_required' => 1,
            'submitted_at' => Carbon::now(),
        ]);
        factory(\App\Models\VisitTransfer\Reference::class)->create([
            'status' => \App\Models\VisitTransfer\Reference::STATUS_REQUESTED,
            'application_id' => $application2->id,
        ]);

        Artisan::call('visit-transfer:cleanup');
        $this->assertEquals(\App\Models\VisitTransfer\Application::STATUS_SUBMITTED, $application1->fresh()->status);
        $this->assertEquals(\App\Models\VisitTransfer\Application::STATUS_SUBMITTED, $application2->fresh()->status);
    }
}
