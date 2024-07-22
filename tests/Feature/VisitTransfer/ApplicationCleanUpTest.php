<?php

namespace Tests\Feature\VisitTransferLegacy;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\NetworkData\Atc;
use App\Models\VisitTransferLegacy\Application;
use App\Models\VisitTransferLegacy\Facility;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApplicationCleanUpTest extends TestCase
{
    use DatabaseTransactions;

    public $application;

    public function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        // Make user an S2
        $this->user = Account::factory()->create();
        $qualification = Qualification::code('S2')->first();
        $this->user->addQualification($qualification);
        $this->user->qualifications()->updateExistingPivot($qualification->id, ['created_at' => new Carbon('100 days ago')]);
        $this->user->save();

        // Create facility & application
        $facility = factory(Facility::class)->states('atc_transfer')->create();
        $this->application = factory(Application::class)->states('atc_transfer')->create([
            'account_id' => $this->user->id,
            'status' => Application::STATUS_SUBMITTED,
            'should_perform_checks' => 1,
            'facility_id' => $facility->id,
            'submitted_at' => now(),
        ]);

        // Add 60 hours of ATC
        $start = new Carbon('80 hours ago');
        $end = new Carbon('20 hours ago');
        factory(Atc::class)->states('offline')->create([
            'account_id' => $this->user->id,
            'qualification_id' => $qualification->id,
            'connected_at' => $start,
            'disconnected_at' => $end,
            'minutes_online' => $start->diffInMinutes($end),
        ]);
    }

    /** @test */
    public function testItWillSet50HourCheckAsPassed()
    {
        $this->assertNull($this->application->check_outcome_50_hours);
        Artisan::call('visit-transfer:cleanup');
        $this->assertTrue($this->application->fresh()->check_outcome_50_hours);
    }

    /** @test */
    public function testItWillSet90DayCheckAsPassed()
    {
        $this->assertNull($this->application->check_outcome_90_day);
        Artisan::call('visit-transfer:cleanup');
        $this->assertTrue($this->application->fresh()->check_outcome_90_day);
    }
}
