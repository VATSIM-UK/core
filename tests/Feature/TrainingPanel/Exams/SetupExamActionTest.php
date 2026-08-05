<?php

namespace Tests\Feature\TrainingPanel\Exams;

use App\Filament\Training\Pages\Exam\Exams;
use App\Models\Atc\Position;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Cts\PracticalResult;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\ExamSetupService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class SetupExamActionTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->panelUser->givePermissionTo('training.exams.access');
    }

    #[Test]
    public function it_shows_the_action_if_authorised()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->assertSuccessful()
            ->assertActionVisible('setupExam');
    }

    #[Test]
    public function it_hides_the_action_if_unauthorised()
    {
        $this->panelUser->revokePermissionTo('training.exams.setup');

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->assertSuccessful()
            ->assertActionHidden('setupExam');
    }

    #[Test]
    public function it_can_setup_exam_for_twr_to_ctr()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        $position = Position::factory()->create([
            'callsign' => 'EGKK_TWR',
            'type' => Position::TYPE_TOWER,
        ]);
        TrainingPosition::factory()->create(['position_id' => $position->id]);

        $studentAccount = Account::factory()->withQualification()->create();
        $student = Member::factory()->forAccount($studentAccount)->create();

        Session::factory()->create([
            'position' => $position->callsign,
            'student_id' => $student->id,
            'taken_date' => now()->subDays(30),
            'cancelled_datetime' => null,
            'noShow' => 0,
            'session_done' => 1,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->callAction('setupExam', data: [
                'twr_position' => $position->id,
                'twr_student' => $student->id,
            ])
            ->assertHasNoActionErrors()
            ->assertNotified();

        $this->assertDatabaseHas('exam_setup', [
            'student_id' => $student->id,
            'position_1' => $position->callsign,
            'setup_by' => $this->panelUser->id,
        ], 'cts');
    }

    #[Test]
    public function it_can_setup_exam_for_obs_pt3()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        $pt3Position = CtsPosition::factory()->create(['callsign' => 'OBS_SC_PT3']);
        $pt2Position = CtsPosition::factory()->create(['callsign' => 'OBS_SC_PT2']);

        $atcPosition = Position::factory()->create(['callsign' => 'SC_GND']);
        $trainingPosition = TrainingPosition::factory()->withCtsPositions([$pt3Position->callsign])->create([
            'position_id' => $atcPosition->id,
            'exam_callsign' => 'OBS_SC_PT2',
        ]);

        $studentAccount = Account::factory()->withQualification()->create();
        $student = Member::factory()->forAccount($studentAccount)->create();

        Session::factory()->create([
            'position' => $pt2Position->callsign,
            'student_id' => $student->id,
            'taken_date' => now()->subDays(30),
            'cancelled_datetime' => null,
            'noShow' => 0,
            'session_done' => 1,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->callAction('setupExam', data: [
                'obs_position' => $pt3Position->id,
                'obs_student' => $student->id,
            ])
            ->assertHasNoActionErrors()
            ->assertNotified();

        $this->assertDatabaseHas('exam_setup', [
            'student_id' => $student->id,
            'position_1' => $trainingPosition->exam_callsign,
            'exam' => 'OBS',
        ], 'cts');
    }

    #[Test]
    public function it_can_setup_exam_for_pilot()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        $studentAccount = Account::factory()->withQualification()->create();
        $student = Member::factory()->forAccount($studentAccount)->create();

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->callAction('setupExam', data: [
                'pilot_exam_type' => 'P1',
                'pilot_student' => $student->id,
            ])
            ->assertHasNoActionErrors()
            ->assertNotified();

        $this->assertDatabaseHas('exam_setup', [
            'student_id' => $student->id,
            'exam' => 'P1',
        ], 'cts');
    }

    #[Test]
    public function it_requires_position_selection_for_twr_to_ctr_tab()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        $student = Member::factory()->create();

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->callAction('setupExam', data: [
                'twr_position' => null,
                'twr_student' => $student->id,
            ])
            ->assertHasActionErrors(['twr_position' => 'required']);
    }

    #[Test]
    public function it_requires_student_selection_for_twr_to_ctr_tab()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        $position = Position::factory()->create(['callsign' => 'EGKK_TWR']);

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->callAction('setupExam', data: [
                'twr_position' => $position->id,
                'twr_student' => null,
            ])
            ->assertHasActionErrors(['twr_student' => 'required']);
    }

    #[Test]
    public function it_requires_position_selection_for_obs_tab()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        $student = Member::factory()->create();

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->callAction('setupExam', data: [
                'obs_position' => null,
                'obs_student' => $student->id,
            ])
            ->assertHasActionErrors(['obs_position' => 'required']);
    }

    #[Test]
    public function it_requires_student_selection_for_obs_tab()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        $position = CtsPosition::factory()->create(['callsign' => 'OBS_SC_PT3']);
        CtsPosition::factory()->create(['callsign' => 'OBS_SC_PT2']);

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->callAction('setupExam', data: [
                'obs_position' => $position->id,
                'obs_student' => null,
            ])
            ->assertHasActionErrors(['obs_student' => 'required']);
    }

    #[Test]
    public function it_requires_exam_type_selection_for_pilot_tab()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        $student = Member::factory()->create();

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->callAction('setupExam', data: [
                'pilot_exam_type' => null,
                'pilot_student' => $student->id,
            ])
            ->assertHasActionErrors(['pilot_exam_type' => 'required']);
    }

    #[Test]
    public function it_requires_student_selection_for_pilot_tab()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->callAction('setupExam', data: [
                'pilot_exam_type' => 'P1',
                'pilot_student' => null,
            ])
            ->assertHasActionErrors(['pilot_student' => 'required']);
    }

    #[Test]
    public function it_does_not_require_other_tabs_when_only_twr_to_ctr_tab_is_filled()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        $position = Position::factory()->create([
            'callsign' => 'EGKK_TWR',
            'type' => Position::TYPE_TOWER,
        ]);
        TrainingPosition::factory()->create(['position_id' => $position->id]);

        $studentAccount = Account::factory()->withQualification()->create();
        $student = Member::factory()->forAccount($studentAccount)->create();

        Session::factory()->create([
            'position' => $position->callsign,
            'student_id' => $student->id,
            'taken_date' => now()->subDays(30),
            'cancelled_datetime' => null,
            'noShow' => 0,
            'session_done' => 1,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->callAction('setupExam', data: [
                'twr_position' => $position->id,
                'twr_student' => $student->id,
            ])
            ->assertHasNoActionErrors();
    }

    #[Test]
    public function it_does_not_include_students_with_pending_exam_in_twr_to_ctr_options()
    {
        $studentAccount = Account::factory()->withQualification()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_TWR']);
        TrainingPosition::factory()->create(['position_id' => $position->id]);

        $student = Member::factory()->forAccount($studentAccount)->create();

        ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => $position->examLevel,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
        ]);

        Session::factory()->create([
            'position' => $position->callsign,
            'student_id' => $student->id,
            'taken_date' => now()->subDays(30),
            'cancelled_datetime' => null,
            'noShow' => 0,
            'session_done' => 1,
        ]);

        $options = app(ExamSetupService::class)->twrToCtrStudentOptions($position->id);

        $this->assertArrayNotHasKey($student->id, $options);
    }

    #[Test]
    public function it_does_not_include_students_with_passed_exam_in_twr_to_ctr_options()
    {
        $studentAccount = Account::factory()->withQualification()->create();
        $student = Member::factory()->forAccount($studentAccount)->create();

        PracticalResult::factory()->create([
            'student_id' => $student->id,
            'exam' => 'TWR',
            'result' => PracticalResult::PASSED,
            'date' => now()->subDays(10),
        ]);

        $position = Position::factory()->create(['callsign' => 'EGKK_TWR']);
        TrainingPosition::factory()->create(['position_id' => $position->id]);

        Session::factory()->create([
            'position' => $position->callsign,
            'student_id' => $student->id,
            'taken_date' => now()->subDays(30),
            'cancelled_datetime' => null,
            'noShow' => 0,
            'session_done' => 1,
        ]);

        $options = app(ExamSetupService::class)->twrToCtrStudentOptions($position->id);

        $this->assertArrayNotHasKey($student->id, $options);
    }

    #[Test]
    public function it_includes_student_with_failed_exam_but_recent_sessions_in_twr_to_ctr_options()
    {
        $studentAccount = Account::factory()->withQualification()->create();
        $student = Member::factory()->forAccount($studentAccount)->create();

        PracticalResult::factory()->create([
            'student_id' => $student->id,
            'exam' => 'TWR',
            'result' => PracticalResult::FAILED,
        ]);

        $position = Position::factory()->create(['callsign' => 'EGKK_TWR']);
        TrainingPosition::factory()->create(['position_id' => $position->id]);

        Session::factory()->create([
            'position' => $position->callsign,
            'student_id' => $student->id,
            'taken_date' => now()->subDays(30),
            'cancelled_datetime' => null,
            'noShow' => 0,
            'session_done' => 1,
        ]);

        $options = app(ExamSetupService::class)->twrToCtrStudentOptions($position->id);

        $this->assertArrayHasKey($student->id, $options);
    }

    #[Test]
    public function it_can_display_the_exams_page_with_the_action_and_tables()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');
        $this->panelUser->givePermissionTo('training.exams.access');

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->assertSuccessful()
            ->assertSeeText('Setup Exam');
    }
}
