<?php

namespace Tests\Unit\VisitTransfer;

use Artisan;
    use Carbon\Carbon;
    use Illuminate\Foundation\Testing\DatabaseTransactions;
    use Tests\TestCase;

    class VisitTransferCleanupTest extends TestCase
    {
        use DatabaseTransactions;

        public $newApplication;
        public $oldApplication;
        public $facility;

        public function setUp()
        {
            parent::setUp();

            $account = factory(\App\Models\Mship\Account::class)->create();
            $this->facility = factory(\App\Models\VisitTransfer\Facility::class, 'atc_visit')->create([
                'stage_reference_enabled' => 1,
                'stage_reference_quantity' => 1,
            ]);
            $application = $account->createVisitingTransferApplication([
                'type' => \App\Models\VisitTransfer\Application::TYPE_VISIT,
                'facility_id' => $this->facility->id,
            ]);
            $this->newApplication = $application->fresh();

            Carbon::setTestNow(Carbon::now()->subHours(3));
            $account = factory(\App\Models\Mship\Account::class)->create();
            $application = $account->createVisitingTransferApplication([
                'type' => \App\Models\VisitTransfer\Application::TYPE_VISIT,
                'facility_id' => $this->facility->id,
            ]);
            Carbon::setTestNow();
            $this->oldApplication = $application->fresh();
        }

        /** @test */
        public function testItOnlyCancelsOldApplications()
        {
            Artisan::call('visit-transfer:cleanup');

            $this->assertEquals(\App\Models\VisitTransfer\Application::STATUS_EXPIRED, $this->oldApplication->fresh()->status);
            $this->assertEquals(\App\Models\VisitTransfer\Application::STATUS_IN_PROGRESS, $this->newApplication->fresh()->status);
        }

        public function testItLapsesApplicationsForOldContactedReferees()
        {
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
    }
