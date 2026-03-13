<?php

namespace Tests\Unit\VisitTransfer;

use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Facility;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VisitTransferCleanupTest extends TestCase
{
    use DatabaseTransactions;

    public $newApplication;

    public $oldApplication;

    public $facility;

    protected function setUp(): void
    {
        parent::setUp();

        // A draft application that has just been started
        $this->facility = Facility::factory()->visit('atc')->create();
        $application = $this->user->createVisitingTransferApplication([
            'type' => Application::TYPE_VISIT,
            'facility_id' => $this->facility->id,
        ]);
        $this->newApplication = $application->fresh();

        // A draft application that was started 3 hours ago
        Carbon::setTestNow(Carbon::now()->subHours(3));
        $user = Account::factory()->create();
        $application = $user->createVisitingTransferApplication([
            'type' => Application::TYPE_VISIT,
            'facility_id' => $this->facility->id,
        ]);
        Carbon::setTestNow();
        $this->oldApplication = $application->fresh();
    }

    #[Test]
    public function it_only_cancels_old_applications()
    {
        Artisan::call('visit-transfer:cleanup');

        $this->assertEquals(Application::STATUS_EXPIRED, $this->oldApplication->fresh()->status);
        $this->assertEquals(Application::STATUS_IN_PROGRESS, $this->newApplication->fresh()->status);
    }
}
