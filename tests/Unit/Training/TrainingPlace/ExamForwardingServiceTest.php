<?php

namespace Tests\Unit\Training\TrainingPlace;

use App\Models\Atc\Position;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamSetup;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Services\Training\ExamForwardingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExamForwardingServiceTest extends TestCase
{
    use DatabaseTransactions;

    private ExamForwardingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ExamForwardingService;
    }

    private function createTestMember(): Member
    {
        $account = Account::factory()->withQualification()->create();

        return Member::factory()->create(['id' => $account->id, 'cid' => $account->id]);
    }

    private function createTestPosition(): Position
    {
        return Position::factory()->create(['callsign' => 'EGKK_TWR']);
    }

    #[Test]
    public function it_creates_exam_setup_and_booking_records()
    {
        $member = $this->createTestMember();
        $position = $this->createTestPosition();
        $userId = Account::factory()->create()->id;

        $result = $this->service->forwardForExam($member, $position, $userId);

        $this->assertInstanceOf(ExamSetup::class, $result['setup']);
        $this->assertInstanceOf(ExamBooking::class, $result['examBooking']);
        $this->assertEquals($result['examBooking']->id, $result['setup']->bookid);
    }

    #[Test]
    public function it_sets_correct_exam_details()
    {
        $member = $this->createTestMember();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);
        $userId = Account::factory()->create()->id;

        $result = $this->service->forwardForExam($member, $position, $userId);

        $this->assertEquals($position->callsign, $result['setup']->position_1);
        $this->assertEquals($position->examLevel, $result['setup']->exam);
        $this->assertNull($result['setup']->position_2);
    }

    #[Test]
    public function it_sets_booking_flags_correctly()
    {
        $member = $this->createTestMember();
        $position = $this->createTestPosition();
        $userId = Account::factory()->create()->id;

        $result = $this->service->forwardForExam($member, $position, $userId);

        $this->assertEquals(ExamBooking::NOT_FINISHED_FLAG, $result['examBooking']->finished);
        $this->assertEquals($member->account->qualification_atc->vatsim, $result['examBooking']->student_rating);
    }

    #[Test]
    public function it_creates_obs_exam_records()
    {
        $member = $this->createTestMember();
        $obsPosition = \App\Models\Cts\Position::factory()->create(['callsign' => 'OBS_SC_PT3']);

        $result = $this->service->forwardForObsExam($member, $obsPosition);

        $this->assertInstanceOf(ExamSetup::class, $result['setup']);
        $this->assertInstanceOf(ExamBooking::class, $result['examBooking']);
        $this->assertEquals('OBS', $result['setup']->exam);
        $this->assertEquals(14, $result['setup']->rts_id);
        $this->assertEquals($result['examBooking']->id, $result['setup']->bookid);
    }

    #[Test]
    public function it_handles_multiple_exams_for_same_member()
    {
        $member = $this->createTestMember();
        $position1 = Position::factory()->create(['callsign' => 'EGKK_TWR']);
        $position2 = Position::factory()->create(['callsign' => 'EGKK_APP']);
        $userId = Account::factory()->create()->id;

        $result1 = $this->service->forwardForExam($member, $position1, $userId);
        $result2 = $this->service->forwardForExam($member, $position2, $userId);

        $this->assertNotEquals($result1['setup']->id, $result2['setup']->id);
        $this->assertNotEquals($result1['examBooking']->id, $result2['examBooking']->id);
    }

    #[Test]
    public function notify_success_does_not_throw()
    {
        $this->service->notifySuccess('EGKK_TWR');
        $this->assertTrue(true);
    }

    #[Test]
    public function notify_error_does_not_throw()
    {
        $this->service->notifyError('Test error');
        $this->assertTrue(true);
    }
}
