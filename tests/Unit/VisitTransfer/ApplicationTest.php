<?php

namespace Tests\Unit\VisitTransfer;

use App\Models\Mship\Qualification;
use App\Models\NetworkData\Atc;
use App\Models\VisitTransfer\Application;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    /** Unit Testing */

    /** @test */
    public function itCanCreateANewApplicationForAUser()
    {
        $account = factory(\App\Models\Mship\Account::class)->create();
        $account->addState(\App\Models\Mship\State::findByCode('INTERNATIONAL'));

        $this->assertCount(0, $account->visitTransferApplications);

        $account->createVisitingTransferApplication([
            'type' => Application::TYPE_VISIT,
        ]);

        $this->assertCount(1, $account->fresh()->visitTransferApplications);
        $this->assertCount(1, $account->fresh()->visitApplications);
    }

    /** @test */
    public function itThrowsAnExceptionWhenAttemptingToCreateADuplicateApplication()
    {
        $this->expectException(\App\Exceptions\VisitTransfer\Application\DuplicateApplicationException::class);

        $account = factory(\App\Models\Mship\Account::class)->create();
        $account->addState(\App\Models\Mship\State::findByCode('INTERNATIONAL'));

        $this->assertCount(0, $account->visitTransferApplications);

        $account->fresh()->createVisitingTransferApplication([
            'type' => Application::TYPE_VISIT,
        ]);

        $account->fresh()->createVisitingTransferApplication([
            'type' => Application::TYPE_VISIT,
        ]);
    }

    /** @test */
    public function itThrowsAnExceptionWhenAttemptingToCreateAnApplicationForADivisionMember()
    {
        $this->expectException(\App\Exceptions\VisitTransfer\Application\AlreadyADivisionMemberException::class);

        $account = factory(\App\Models\Mship\Account::class)->create();
        $account->addState(\App\Models\Mship\State::findByCode('DIVISION'));

        $this->assertCount(0, $account->visitTransferApplications);

        $account->fresh()->createVisitingTransferApplication([
            'type' => Application::TYPE_VISIT,
        ]);
    }

    /** @test */
    public function itCorrectlyReports50HourCheck()
    {
        Mail::fake();

        $account = factory(\App\Models\Mship\Account::class)->create();
        $qual = Qualification::code('S2')->first();
        $account->addQualification($qual);
        $account->save();

        $application = factory(Application::class, 'atc_transfer')->create([
            'account_id' => $account->id,
            'status' => Application::STATUS_SUBMITTED,
            'should_perform_checks' => 1,
        ]);

        // Add 49 hours of ATC
        $start = new Carbon('80 hours ago');
        $end = new Carbon('31 hours ago');
        factory(Atc::class, 'offline')->create([
            'account_id' => $account->id,
            'qualification_id' => $qual->id,
            'connected_at' => $start,
            'disconnected_at' => $end,
            'minutes_online' => $start->diffInMinutes($end),
        ]);

        $this->assertFalse($application->check50Hours());

        // Add 1 hours of ATC
        $start = new Carbon('2 hours ago');
        $end = new Carbon('1 hour ago');
        factory(Atc::class, 'offline')->create([
            'account_id' => $account->id,
            'qualification_id' => $qual->id,
            'connected_at' => $start,
            'disconnected_at' => $end,
            'minutes_online' => $start->diffInMinutes($end),
        ]);

        $this->assertTrue($application->check50Hours());
    }

    /** @test */
    public function itDisregardsAtcOfDifferentQualificationFor50HourCheck()
    {
        Mail::fake();

        $account = factory(\App\Models\Mship\Account::class)->create();
        $qual = Qualification::code('S2')->first();
        $account->addQualification($qual);
        $account->save();

        $application = factory(Application::class, 'atc_transfer')->create([
            'account_id' => $account->id,
            'status' => Application::STATUS_SUBMITTED,
            'should_perform_checks' => 1,
        ]);

        // Add 60 hours of ATC
        $start = new Carbon('80 hours ago');
        $end = new Carbon('20 hours ago');
        factory(Atc::class, 'offline')->create([
            'account_id' => $account->id,
            'qualification_id' => Qualification::code('S1')->first()->id,
            'connected_at' => $start,
            'disconnected_at' => $end,
            'minutes_online' => $start->diffInMinutes($end),
        ]);

        $this->assertFalse($application->check50Hours());
    }

    /** @test */
    public function itCorrectlyReports90DayCheck()
    {
        $account = factory(\App\Models\Mship\Account::class)->create();
        $qual = Qualification::code('S2')->first();
        $account->addQualification($qual);
        $account->save();

        $application = factory(Application::class, 'atc_transfer')->create([
            'account_id' => $account->id,
            'status' => Application::STATUS_SUBMITTED,
            'should_perform_checks' => 1,
            'submitted_at' => now(),
        ]);

        $this->assertFalse($application->fresh()->check90DayQualification());
        $account->qualifications()->updateExistingPivot($qual->id, ['created_at' => new Carbon('100 days ago')]);
        $this->assertTrue($application->fresh()->check90DayQualification());
    }
}
