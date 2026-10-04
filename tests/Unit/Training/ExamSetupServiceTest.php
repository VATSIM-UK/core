<?php

namespace Tests\Unit\Training;

use App\Models\Atc\Position;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Cts\PracticalResult;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\ExamSetupService;
use App\Services\Training\PendingExamExistsException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExamSetupServiceTest extends TestCase
{
    use DatabaseTransactions;

    private ExamSetupService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ExamSetupService;
    }

    #[Test]
    public function it_returns_twr_to_ctr_positions_excluding_atis()
    {
        $twr = Position::factory()->create(['callsign' => 'EGKK_TWR']);
        $app = Position::factory()->create(['callsign' => 'EGLL_APP']);
        $atis = Position::factory()->create(['callsign' => 'EGLL_ATIS']);

        $options = $this->service->twrToCtrPositionOptions();

        $this->assertArrayHasKey($app->id, $options);
        $this->assertSame('EGLL_APP', $options[$app->id]);
        $this->assertArrayHasKey($twr->id, $options);
        $this->assertSame('EGKK_TWR', $options[$twr->id]);
        $this->assertArrayNotHasKey($atis->id, $options);
    }

    #[Test]
    public function it_returns_empty_twr_to_ctr_student_options_when_no_position_selected()
    {
        $this->assertSame([], $this->service->twrToCtrStudentOptions(null));
    }

    #[Test]
    public function it_returns_empty_twr_to_ctr_student_options_when_position_does_not_exist()
    {
        $this->assertSame([], $this->service->twrToCtrStudentOptions(999999));
    }

    #[Test]
    public function it_returns_students_with_recent_completed_sessions_as_twr_to_ctr_options()
    {
        $position = $this->createTwrToCtrPosition();
        $student = $this->createStudentWithAccount();
        $this->createCompletedSession($student, $position->callsign);

        $options = $this->service->twrToCtrStudentOptions($position->id);

        $this->assertArrayHasKey($student->id, $options);
        $this->assertSame("{$student->name} ({$student->cid})", $options[$student->id]);
    }

    #[Test]
    public function it_excludes_students_with_pending_exam_from_twr_to_ctr_options()
    {
        $position = $this->createTwrToCtrPosition();
        $student = $this->createStudentWithAccount();
        $this->createCompletedSession($student, $position->callsign);

        ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => $position->examLevel,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
        ]);

        $this->assertArrayNotHasKey($student->id, $this->service->twrToCtrStudentOptions($position->id));
    }

    #[Test]
    public function it_excludes_students_with_recently_passed_exam_from_twr_to_ctr_options()
    {
        $position = $this->createTwrToCtrPosition();
        $student = $this->createStudentWithAccount();
        $this->createCompletedSession($student, $position->callsign);

        PracticalResult::factory()->create([
            'student_id' => $student->id,
            'exam' => $position->examLevel,
            'result' => PracticalResult::PASSED,
            'date' => now()->subDays(10),
        ]);

        $this->assertArrayNotHasKey($student->id, $this->service->twrToCtrStudentOptions($position->id));
    }

    #[Test]
    public function it_includes_students_with_failed_exam_in_twr_to_ctr_options()
    {
        $position = $this->createTwrToCtrPosition();
        $student = $this->createStudentWithAccount();
        $this->createCompletedSession($student, $position->callsign);

        PracticalResult::factory()->create([
            'student_id' => $student->id,
            'exam' => $position->examLevel,
            'result' => PracticalResult::FAILED,
            'date' => now()->subDays(10),
        ]);

        $options = $this->service->twrToCtrStudentOptions($position->id);

        $this->assertArrayHasKey($student->id, $options);
    }

    #[Test]
    public function it_ignores_sessions_outside_recent_window_for_twr_to_ctr_options()
    {
        $position = $this->createTwrToCtrPosition();
        $student = $this->createStudentWithAccount();
        $this->createCompletedSession($student, $position->callsign, now()->subDays(200));

        $this->assertArrayNotHasKey($student->id, $this->service->twrToCtrStudentOptions($position->id));
    }

    #[Test]
    public function it_ignores_sessions_that_are_not_completed_for_twr_to_ctr_options()
    {
        $position = $this->createTwrToCtrPosition();
        $student = $this->createStudentWithAccount();

        Session::factory()->create([
            'position' => $position->callsign,
            'student_id' => $student->id,
            'taken_date' => now()->subDays(30),
            'cancelled_datetime' => null,
            'noShow' => 0,
            'session_done' => 0,
        ]);

        $this->assertArrayNotHasKey($student->id, $this->service->twrToCtrStudentOptions($position->id));
    }

    #[Test]
    public function it_forwards_student_for_twr_to_ctr_exam()
    {
        $position = $this->createTwrToCtrPosition();
        $student = $this->createStudentWithAccount();
        $forwardedBy = Account::factory()->create();

        $this->service->setupTwrToCtr($position->id, $student->id, $forwardedBy->id);

        $this->assertDatabaseHas('exam_setup', [
            'student_id' => $student->id,
            'position_1' => $position->callsign,
            'exam' => $position->examLevel,
            'setup_by' => $forwardedBy->id,
        ], 'cts');
        $this->assertDatabaseHas('exam_book', [
            'student_id' => $student->id,
            'position_1' => $position->callsign,
            'exam' => $position->examLevel,
        ], 'cts');
    }

    #[Test]
    public function it_uses_exam_callsign_override_for_twr_to_ctr_exam()
    {
        $position = Position::factory()->create(['callsign' => 'EGCC_S_APP']);
        TrainingPosition::factory()->create([
            'position_id' => $position->id,
            'exam_callsign' => 'EGCC_APP',
        ]);
        $student = $this->createStudentWithAccount();

        $this->service->setupTwrToCtr($position->id, $student->id, $this->user->id);

        $this->assertDatabaseHas('exam_setup', [
            'student_id' => $student->id,
            'position_1' => 'EGCC_APP',
            'exam' => 'APP',
        ], 'cts');
    }

    #[Test]
    public function it_throws_when_student_has_pending_twr_to_ctr_exam()
    {
        $position = $this->createTwrToCtrPosition();
        $student = $this->createStudentWithAccount();

        ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => $position->examLevel,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
        ]);

        $this->expectException(PendingExamExistsException::class);

        $this->service->setupTwrToCtr($position->id, $student->id, $this->user->id);
    }

    #[Test]
    public function it_throws_when_training_position_does_not_exist_for_twr_to_ctr()
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_TWR']);
        $student = $this->createStudentWithAccount();

        $this->expectException(ModelNotFoundException::class);

        $this->service->setupTwrToCtr($position->id, $student->id, $this->user->id);
    }

    #[Test]
    public function it_throws_when_student_does_not_exist_for_twr_to_ctr()
    {
        $position = $this->createTwrToCtrPosition();

        $this->expectException(ModelNotFoundException::class);

        $this->service->setupTwrToCtr($position->id, 999999999, $this->user->id);
    }

    #[Test]
    public function it_returns_obs_pt3_positions_ordered_by_callsign()
    {
        $pt3sc = CtsPosition::factory()->create(['callsign' => 'OBS_SC_PT3']);
        CtsPosition::factory()->create(['callsign' => 'OBS_SC_PT2']);
        $pt3ld = CtsPosition::factory()->create(['callsign' => 'OBS_LD_PT3']);

        $options = $this->service->obsPositionOptions();

        $this->assertEquals([
            $pt3ld->id => 'OBS_LD_PT3',
            $pt3sc->id => 'OBS_SC_PT3',
        ], $options);
    }

    #[Test]
    public function it_returns_empty_obs_student_options_when_no_position_selected()
    {
        $this->assertSame([], $this->service->obsStudentOptions(null));
    }

    #[Test]
    public function it_returns_empty_obs_student_options_when_position_does_not_exist()
    {
        $this->assertSame([], $this->service->obsStudentOptions(999999));
    }

    #[Test]
    public function it_returns_empty_obs_student_options_when_pt2_position_not_found()
    {
        $pt3 = CtsPosition::factory()->create(['callsign' => 'OBS_SC_PT3']);

        $this->assertSame([], $this->service->obsStudentOptions($pt3->id));
    }

    #[Test]
    public function it_returns_students_with_recent_completed_pt2_sessions_as_obs_options()
    {
        $pt3 = CtsPosition::factory()->create(['callsign' => 'OBS_SC_PT3']);
        $pt2 = CtsPosition::factory()->create(['callsign' => 'OBS_SC_PT2']);
        $student = $this->createStudentWithAccount();
        $this->createCompletedSession($student, $pt2->callsign);

        $options = $this->service->obsStudentOptions($pt3->id);

        $this->assertEquals([
            $student->id => "{$student->name} ({$student->cid})",
        ], $options);
    }

    #[Test]
    public function it_excludes_students_with_pending_obs_exam_from_obs_options()
    {
        $pt3 = CtsPosition::factory()->create(['callsign' => 'OBS_SC_PT3']);
        $pt2 = CtsPosition::factory()->create(['callsign' => 'OBS_SC_PT2']);
        $student = $this->createStudentWithAccount();
        $this->createCompletedSession($student, $pt2->callsign);

        ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => 'OBS',
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
        ]);

        $this->assertSame([], $this->service->obsStudentOptions($pt3->id));
    }

    #[Test]
    public function it_excludes_students_with_recently_passed_obs_exam_from_obs_options()
    {
        $pt3 = CtsPosition::factory()->create(['callsign' => 'OBS_SC_PT3']);
        $pt2 = CtsPosition::factory()->create(['callsign' => 'OBS_SC_PT2']);
        $student = $this->createStudentWithAccount();
        $this->createCompletedSession($student, $pt2->callsign);

        PracticalResult::factory()->create([
            'student_id' => $student->id,
            'exam' => 'OBS',
            'result' => PracticalResult::PASSED,
            'date' => now()->subDays(10),
        ]);

        $this->assertSame([], $this->service->obsStudentOptions($pt3->id));
    }

    #[Test]
    public function it_forwards_student_for_obs_exam()
    {
        $pt3 = CtsPosition::factory()->create(['callsign' => 'OBS_SC_PT3']);
        $atcPosition = Position::factory()->create(['callsign' => 'SC_GND']);
        $trainingPosition = TrainingPosition::factory()->withCtsPositions([$pt3->callsign])->create([
            'position_id' => $atcPosition->id,
            'exam_callsign' => 'OBS_SC_PT2',
        ]);
        $student = $this->createStudentWithAccount();

        $this->service->setupObs($pt3->id, $student->id);

        $this->assertDatabaseHas('exam_setup', [
            'student_id' => $student->id,
            'position_1' => $trainingPosition->exam_callsign,
            'exam' => 'OBS',
        ], 'cts');
        $this->assertDatabaseHas('exam_book', [
            'student_id' => $student->id,
            'position_1' => $trainingPosition->exam_callsign,
            'exam' => 'OBS',
        ], 'cts');
    }

    #[Test]
    public function it_throws_when_student_has_pending_obs_exam()
    {
        $pt3 = CtsPosition::factory()->create(['callsign' => 'OBS_SC_PT3']);
        $atcPosition = Position::factory()->create(['callsign' => 'SC_GND']);
        TrainingPosition::factory()->withCtsPositions([$pt3->callsign])->create([
            'position_id' => $atcPosition->id,
            'exam_callsign' => 'OBS_SC_PT2',
        ]);
        $student = $this->createStudentWithAccount();

        ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => 'OBS',
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
        ]);

        $this->expectException(PendingExamExistsException::class);

        $this->service->setupObs($pt3->id, $student->id);
    }

    #[Test]
    public function it_throws_when_no_training_position_matches_obs_position()
    {
        $pt3 = CtsPosition::factory()->create(['callsign' => 'OBS_SC_PT3']);
        $student = $this->createStudentWithAccount();

        $this->expectException(ModelNotFoundException::class);

        $this->service->setupObs($pt3->id, $student->id);
    }

    #[Test]
    public function it_returns_pilot_exam_type_options()
    {
        $this->assertEquals([
            'P1' => 'P1_PPL(A)',
            'P2' => 'P2_SEIR(A)',
            'P3' => 'P3_CMEL(A)',
        ], $this->service->pilotExamTypeOptions());
    }

    #[Test]
    public function it_returns_empty_pilot_search_results_when_no_exam_type_selected()
    {
        $this->assertSame([], $this->service->pilotStudentSearchResults('anything', null));
    }

    #[Test]
    public function it_returns_empty_pilot_search_results_when_no_members_match_search()
    {
        $this->assertSame([], $this->service->pilotStudentSearchResults('no-such-member', 'P1'));
    }

    #[Test]
    public function it_returns_eligible_students_holding_prerequisite_qualification()
    {
        $student = $this->createPilotStudent('Alice Johnson', 'P0');

        $options = $this->service->pilotStudentSearchResults('Alice', 'P1');

        $this->assertEquals([
            $student->id => "Alice Johnson ({$student->cid})",
        ], $options);
    }

    #[Test]
    public function it_includes_students_holding_flight_examiner_qualification()
    {
        $student = $this->createPilotStudent('Alice Johnson', 'FE');

        $options = $this->service->pilotStudentSearchResults('Alice', 'P1');

        $this->assertArrayHasKey($student->id, $options);
    }

    #[Test]
    public function it_excludes_students_without_prerequisite_qualification()
    {
        $account = Account::factory()->withQualification()->create();
        Member::factory()->forAccount($account)->create(['name' => 'Alice Johnson']);

        $this->assertSame([], $this->service->pilotStudentSearchResults('Alice', 'P1'));
    }

    #[Test]
    public function it_uses_exam_type_specific_prerequisite_qualification()
    {
        $p2Student = $this->createPilotStudent('Bob Baker', 'PPL');
        $p1Student = $this->createPilotStudent('Alice Johnson', 'P0');

        $options = $this->service->pilotStudentSearchResults('Bob', 'P2');

        $this->assertArrayHasKey($p2Student->id, $options);
        $this->assertArrayNotHasKey($p1Student->id, $options);
    }

    #[Test]
    public function it_excludes_students_who_have_passed_the_exam_since_2020()
    {
        $student = $this->createPilotStudent('Alice Johnson', 'P0');

        PracticalResult::factory()->create([
            'student_id' => $student->id,
            'exam' => 'P1',
            'result' => PracticalResult::PASSED,
            'date' => Carbon::parse('2021-01-01'),
        ]);

        $this->assertSame([], $this->service->pilotStudentSearchResults('Alice', 'P1'));
    }

    #[Test]
    public function it_ignores_exam_passes_before_2020()
    {
        $student = $this->createPilotStudent('Alice Johnson', 'P0');

        PracticalResult::factory()->create([
            'student_id' => $student->id,
            'exam' => 'P1',
            'result' => PracticalResult::PASSED,
            'date' => Carbon::parse('2019-06-15'),
        ]);

        $options = $this->service->pilotStudentSearchResults('Alice', 'P1');

        $this->assertArrayHasKey($student->id, $options);
    }

    #[Test]
    public function it_excludes_students_with_pending_pilot_exam()
    {
        $student = $this->createPilotStudent('Alice Johnson', 'P0');

        ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => 'P1',
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
        ]);

        $this->assertSame([], $this->service->pilotStudentSearchResults('Alice', 'P1'));
    }

    #[Test]
    public function it_matches_pilot_search_by_cid()
    {
        $student = $this->createPilotStudent('Alice Johnson', 'P0');

        $options = $this->service->pilotStudentSearchResults((string) $student->cid, 'P1');

        $this->assertArrayHasKey($student->id, $options);
    }

    #[Test]
    public function it_returns_student_name_for_pilot_label()
    {
        $student = $this->createStudentWithAccount();

        $this->assertSame($student->name, $this->service->pilotStudentLabel($student->id));
    }

    #[Test]
    public function it_returns_null_for_pilot_label_when_student_does_not_exist()
    {
        $this->assertNull($this->service->pilotStudentLabel(999999999));
    }

    #[Test]
    public function it_forwards_student_for_pilot_exam()
    {
        $student = $this->createStudentWithAccount();
        $forwardedBy = Account::factory()->create();

        $this->service->setupPilot('P1', $student->id, $forwardedBy->id);

        $this->assertDatabaseHas('exam_setup', [
            'student_id' => $student->id,
            'exam' => 'P1',
            'position_1' => 'P1_PPL(A)',
            'setup_by' => $forwardedBy->id,
        ], 'cts');
        $this->assertDatabaseHas('exam_book', [
            'student_id' => $student->id,
            'exam' => 'P1',
            'position_1' => 'P1_PPL(A)',
        ], 'cts');
    }

    #[Test]
    public function it_throws_when_student_has_pending_pilot_exam()
    {
        $student = $this->createStudentWithAccount();

        ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => 'P1',
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
        ]);

        $this->expectException(PendingExamExistsException::class);

        $this->service->setupPilot('P1', $student->id, $this->user->id);
    }

    #[Test]
    public function it_throws_when_student_has_pending_exam()
    {
        $student = $this->createStudentWithAccount();

        ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => 'TWR',
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
        ]);

        $this->expectException(PendingExamExistsException::class);

        $this->service->guardAgainstPendingExam($student, 'TWR');
    }

    #[Test]
    public function it_does_not_throw_when_student_has_no_pending_exam()
    {
        $student = $this->createStudentWithAccount();

        $this->service->guardAgainstPendingExam($student, 'TWR');

        $this->assertTrue(true);
    }

    #[Test]
    public function it_does_not_throw_when_pending_exam_is_of_different_type()
    {
        $student = $this->createStudentWithAccount();

        ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => 'TWR',
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
        ]);

        $this->service->guardAgainstPendingExam($student, 'APP');

        $this->assertTrue(true);
    }

    private function createTwrToCtrPosition(string $callsign = 'EGKK_TWR'): Position
    {
        $position = Position::factory()->create(['callsign' => $callsign]);
        TrainingPosition::factory()->create(['position_id' => $position->id]);

        return $position;
    }

    private function createStudentWithAccount(): Member
    {
        $account = Account::factory()->withQualification()->create();

        return Member::factory()->forAccount($account)->create();
    }

    private function createPilotStudent(string $name, string $qualificationCode): Member
    {
        $account = Account::factory()->create();
        $account->qualifications()->attach(
            Qualification::firstWhere('code', $qualificationCode)
                ?? Qualification::factory()->create(['code' => $qualificationCode, 'type' => 'pilot'])
        );

        return Member::factory()->forAccount($account)->create(['name' => $name]);
    }

    private function createCompletedSession(Member $student, string $positionCallsign, ?Carbon $takenDate = null): Session
    {
        return Session::factory()->create([
            'position' => $positionCallsign,
            'student_id' => $student->id,
            'taken_date' => $takenDate ?? now()->subDays(30),
            'cancelled_datetime' => null,
            'noShow' => 0,
            'session_done' => 1,
        ]);
    }
}
