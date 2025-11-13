<?php

namespace Tests\Feature\TrainingPanel\Exams;

use App\Filament\Training\Pages\Exam\ExamSetup;
use App\Models\Atc\Position;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Cts\PracticalResult;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ExamSetupTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_loads_if_authorised()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        Livewire::actingAs($this->panelUser)
            ->test(ExamSetup::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_does_not_load_if_unauthorised()
    {
        // User only has basic training.access but not training.exams.setup
        $this->panelUser->revokePermissionTo('training.exams.setup');

        Livewire::actingAs($this->panelUser)
            ->test(ExamSetup::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_can_setup_exam_for_twr_to_ctr()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        // Create a position for testing
        $position = Position::factory()->create([
            'callsign' => 'EGKK_TWR',
            'type' => Position::TYPE_TOWER,
        ]);

        // Create a student account and member
        $studentAccount = Account::factory()->withQualification()->create();
        $student = Member::factory()->create([
            'id' => $studentAccount->id,
            'cid' => $studentAccount->id,
        ]);

        // Create a recent completed session for the student
        Session::factory()->create([
            'position' => $position->callsign,
            'student_id' => $student->id,
            'taken_date' => now()->subDays(30),
            'cancelled_datetime' => null,
            'noShow' => 0,
            'session_done' => 1,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ExamSetup::class)
            ->set('data.position', $position->id)
            ->set('data.student', $student->id)
            ->call('setupExam')
            ->assertHasNoErrors()
            ->assertNotified();

        // Verify that records were created (basic verification)
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

        // Create OBS positions
        $pt3Position = CtsPosition::factory()->create([
            'callsign' => 'OBS_SC_PT3',
        ]);

        $pt2Position = CtsPosition::factory()->create([
            'callsign' => 'OBS_SC_PT2',
        ]);

        // Create a student account and member
        $studentAccount = Account::factory()->withQualification()->create();
        $student = Member::factory()->create([
            'id' => $studentAccount->id,
            'cid' => $studentAccount->id,
        ]);

        // Create a recent completed session for the student on PT2 position
        Session::factory()->create([
            'position' => $pt2Position->callsign,
            'student_id' => $student->id,
            'taken_date' => now()->subDays(30),
            'cancelled_datetime' => null,
            'noShow' => 0,
            'session_done' => 1,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ExamSetup::class)
            ->set('dataOBS.position_obs', $pt3Position->id)
            ->set('dataOBS.student_obs', $student->id)
            ->call('setupExamOBS')
            ->assertHasNoErrors()
            ->assertNotified();

        // Verify that OBS exam setup was created
        $this->assertDatabaseHas('exam_setup', [
            'student_id' => $student->id,
            'position_1' => $pt3Position->callsign,
            'exam' => 'OBS',
        ], 'cts');
    }

    #[Test]
    public function it_requires_position_selection_for_twr_to_ctr_form()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        Livewire::actingAs($this->panelUser)
            ->test(ExamSetup::class)
            ->set('data.position', null)
            ->set('data.student', null)
            ->call('setupExam')
            ->assertHasFormErrors(['position' => 'required']);
    }

    #[Test]
    public function it_requires_student_selection_for_twr_to_ctr_form()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        $position = Position::factory()->create([
            'callsign' => 'EGKK_TWR',
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ExamSetup::class)
            ->fillForm([
                'data.position' => $position->id,
                'data.student' => null,
            ])
            ->call('setupExam')
            ->assertHasFormErrors(['student' => 'required']);
    }

    #[Test]
    public function it_requires_position_selection_for_obs_form()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        Livewire::actingAs($this->panelUser)
            ->test(ExamSetup::class)
            ->set('dataOBS.position_obs', null)
            ->call('setupExamOBS')
            ->assertHasFormErrors(['position_obs' => 'required'], formName: 'formOBS');
    }

    #[Test]
    public function it_requires_student_selection_for_obs_form()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        $position = CtsPosition::factory()->create([
            'callsign' => 'OBS_SC_PT3',
        ]);

        $pt2Position = CtsPosition::factory()->create([
            'callsign' => 'OBS_SC_PT2',
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ExamSetup::class)
            ->set('dataOBS.position_obs', $position->id)
            ->set('dataOBS.student_obs', null)
            ->call('setupExamOBS')
            ->assertHasFormErrors(['student_obs' => 'required'], formName: 'formOBS');
    }

    #[Test]
    public function it_does_not_show_students_with_pending_exam_in_twr_to_ctr_form()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        $studentAccount = Account::factory()->withQualification()->create();
        $position = Position::factory()->create([
            'callsign' => 'EGKK_TWR',
        ]);

        $student = Member::factory()->create([
            'id' => $studentAccount->id,
            'cid' => $studentAccount->id,
        ]);

        $pendingExam = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => $position->examLevel,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ExamSetup::class)
            ->set('data.position', $position->id)
            ->set('data.student', $student->id)
            ->call('setupExam')
            ->assertDontSee($student->id);
    }

    #[Test]
    public function it_does_not_show_students_with_pending_exam_in_obs_form()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        $studentAccount = Account::factory()->withQualification()->create();
        $student = Member::factory()->create([
            'id' => $studentAccount->id,
            'cid' => $studentAccount->id,
        ]);

        $pendingExam = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'exam' => 'OBS',
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
        ]);

        $position = CtsPosition::factory()->create([
            'callsign' => 'OBS_SC_PT3',
        ]);

        $pt2Position = CtsPosition::factory()->create([
            'callsign' => 'OBS_SC_PT2',
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ExamSetup::class)
            ->set('dataOBS.position_obs', $position->id)
            ->set('dataOBS.student_obs', $student->id)
            ->call('setupExamOBS')
            ->assertDontSee($student->id);
    }

    #[Test]
    public function it_does_not_show_students_with_passed_exam_in_twr_to_ctr_form()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        $studentAccount = Account::factory()->withQualification()->create();
        $student = Member::factory()->create([
            'id' => $studentAccount->id,
            'cid' => $studentAccount->id,
        ]);

        PracticalResult::factory()->create([
            'student_id' => $student->id,
            'exam' => 'TWR',
            'result' => PracticalResult::PASSED,
        ]);

        $position = Position::factory()->create([
            'callsign' => 'EGKK_TWR',
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ExamSetup::class)
            ->set('data.position', $position->id)
            ->set('data.student', $student->id)
            ->call('setupExam')
            ->assertDontSee($student->id);
    }

    #[Test]
    public function it_does_show_student_with_failed_exam_but_recent_sessions_in_twr_to_ctr_form()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        $studentAccount = Account::factory()->withQualification()->create();
        $student = Member::factory()->create([
            'id' => $studentAccount->id,
            'cid' => $studentAccount->id,
        ]);

        PracticalResult::factory()->create([
            'student_id' => $student->id,
            'exam' => 'TWR',
            'result' => PracticalResult::FAILED,
        ]);

        $position = Position::factory()->create([
            'callsign' => 'EGKK_TWR',
        ]);

        Session::factory()->create([
            'position' => $position->callsign,
            'student_id' => $student->id,
            'taken_date' => now()->subDays(30),
            'cancelled_datetime' => null,
            'noShow' => 0,
            'session_done' => 1,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ExamSetup::class)
            ->set('data.position', $position->id)
            ->set('data.student', $student->id)
            ->call('setupExam')
            ->assertSee($student->id);
    }

    #[Test]
    public function it_can_display_both_forms_correctly()
    {
        $this->panelUser->givePermissionTo('training.exams.setup');

        // Test that both forms are rendered
        Livewire::actingAs($this->panelUser)
            ->test(ExamSetup::class)
            ->assertSuccessful()
            ->assertSeeText('Exam Setup - TWR to CTR')
            ->assertSeeText('Exam Setup - OBS PT3');
    }
}
