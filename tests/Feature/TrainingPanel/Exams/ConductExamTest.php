<?php

namespace Tests\Feature\TrainingPanel\Exams;

use App\Filament\Training\Pages\ConductExam;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamCriteria;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ConductExamTest extends BaseTrainingPanelTestCase
{
    #[Test]
    public function it_loads_if_authorised()
    {

        $account = Account::factory()->create();
        $student = factory(Member::class)->create(['id' => $account->id, 'cid' => $account->id]);
        $exam = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'TWR',
            'student_id' => $student->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
        ]);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertSuccessful();
    }

    #[Test]
    public function it_does_not_load_if_unauthorised()
    {
        $exam = ExamBooking::factory()->create(['taken' => 1, 'finished' => ExamBooking::NOT_FINISHED_FLAG, 'exam' => 'TWR']);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertForbidden();
    }

    #[Test]
    public function test_unauthorised_when_access_to_other_type_of_exam()
    {
        $exam = ExamBooking::factory()->create(['taken' => 1, 'finished' => ExamBooking::NOT_FINISHED_FLAG, 'exam' => 'APP']);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertForbidden();
    }

    #[Test]
    public function test_can_fill_out_comments_one_of_criteria()
    {
        $account = Account::factory()->create();
        $student = factory(Member::class)->create(['id' => $account->id, 'cid' => $account->id]);
        $exam = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'TWR',
            'student_id' => $student->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
        ]);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        $examCriteria = ExamCriteria::create([
            'exam' => 'TWR',
            'criteria' => 'Test Criteria',
            'deleted' => 0,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertSuccessful()
            ->set("data.form.{$examCriteria->id}.comments", 'Test comment for test criteria')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('practical_criteria_assess', connection: 'cts', data: [
            'examid' => $exam->id,
            'criteria_id' => $examCriteria->id,
            'notes' => 'Test comment for test criteria',
            'result' => 'N',
        ]);
    }

    #[Test]
    public function test_can_change_grade_on_one_of_criteria()
    {
        $account = Account::factory()->create();
        $student = factory(Member::class)->create(['id' => $account->id, 'cid' => $account->id]);
        $exam = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'exam' => 'TWR',
            'student_id' => $student->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
        ]);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        $this->panelUser->givePermissionTo('training.exams.conduct.twr');

        $examCriteria = ExamCriteria::create([
            'exam' => 'TWR',
            'criteria' => 'Test Criteria',
            'deleted' => 0,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(ConductExam::class, ['examId' => $exam->id])
            ->assertSuccessful()
            ->set("data.form.{$examCriteria->id}.grade", 'P')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('practical_criteria_assess', connection: 'cts', data: [
            'examid' => $exam->id,
            'criteria_id' => $examCriteria->id,
            'notes' => '',
            'result' => 'P',
        ]);
    }
}
