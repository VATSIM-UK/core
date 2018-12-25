<?php

namespace Tests\Feature\VisitTransfer;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
use App\Models\Mship\Qualification;
use Illuminate\Support\Facades\Mail;
use App\Models\VisitTransfer\Facility;
use Illuminate\Support\Facades\Artisan;
use App\Models\VisitTransfer\Application;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApplicationCleanUpTest extends TestCase
{
    use DatabaseTransactions;

    public $application;
    public $user;

    public function setUp()
    {
        parent::setUp();
        Mail::fake();

        $qual = Qualification::code('S2')->first();
        $this->user = factory(Account::class)->create();
        $this->user->addQualification($qual);
        $this->user->qualifications()->updateExistingPivot($qual->id, ['created_at' => new Carbon('100 days ago')]);
        $this->user->save();

        $facility = factory(Facility::class, 'atc_transfer')->create();
        $this->application = factory(Application::class, 'atc_transfer')->create([
            'account_id' => $this->user->id,
            'status' => Application::STATUS_SUBMITTED,
            'should_perform_checks' => 1,
            'facility_id' => $facility->id,
            'submitted_at' => now(),
        ]);

        // Add 60 hours of ATC
        $start = new Carbon('80 hours ago');
        $end = new Carbon('20 hours ago');
        factory(Atc::class, 'offline')->create([
            'account_id' => $this->user->id,
            'qualification_id' => $qual->id,
            'connected_at' => $start,
            'disconnected_at' => $end,
            'minutes_online' => $start->diffInMinutes($end),
        ]);
    }

    /** @test * */
    public function testItWillSet50HourCheckAsPassed()
    {
        $this->assertNull($this->application->check_outcome_50_hours);
        Artisan::call('visit-transfer:cleanup');
        $this->assertTrue($this->application->fresh()->check_outcome_50_hours);
    }

    /** @test * */
    public function testItWillSet90DayCheckAsPassed()
    {
        $this->assertNull($this->application->check_outcome_90_day);
        Artisan::call('visit-transfer:cleanup');
        $this->assertTrue($this->application->fresh()->check_outcome_90_day);
    }
}
