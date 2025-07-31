<?php

namespace Tests\Feature\TrainingPanel\Exams;

use App\Filament\Training\Pages\ConductExam;
use App\Filament\Training\Pages\Exams;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ExamsOverviewTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_loads_if_authorised()
    {
        $this->panelUser->givePermissionTo('training.exams.access');

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_does_not_load_if_unauthorised()
    {
        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->assertForbidden();
    }

    #[Test]
    public function test_can_see_exam_when_assigned_as_primary_examiner()
    {
        $this->panelUser->givePermissionTo('training.exams.access');

        $student = Account::factory()->create();
        Member::factory()->create(['id' => $student->id, 'cid' => $student->id]);

        $exam = ExamBooking::factory()->create(['taken' => 1, 'finished' => ExamBooking::NOT_FINISHED_FLAG, 'student_id' => $student->id]);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->assertSuccessful()
            ->assertSee($exam->student->cid)
            ->assertSee($exam->position_1)
            ->assertTableActionEnabled('Conduct', $exam);
    }

    #[Test]
    public function test_can_see_exam_when_assigned_as_other_examiner()
    {
        $this->panelUser->givePermissionTo('training.exams.access');

        $student = Account::factory()->create();
        Member::factory()->create(['id' => $student->id, 'cid' => $student->id]);

        $exam = ExamBooking::factory()->create(['taken' => 1, 'finished' => ExamBooking::NOT_FINISHED_FLAG, 'student_id' => $student->id]);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'other' => $this->panelUser->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->assertSuccessful()
            ->assertSee($exam->student->cid)
            ->assertSee($exam->position_1)
            ->assertTableActionEnabled('Conduct', $exam);
    }

    #[Test]
    public function test_can_see_exam_when_assigned_as_trainee_examiner()
    {
        $this->panelUser->givePermissionTo('training.exams.access');

        $student = Account::factory()->create();
        Member::factory()->create(['id' => $student->id, 'cid' => $student->id]);

        $exam = ExamBooking::factory()->create(['taken' => 1, 'finished' => ExamBooking::NOT_FINISHED_FLAG, 'student_id' => $student->id]);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'trainee' => $this->panelUser->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->assertSuccessful()
            ->assertSee($exam->student->cid)
            ->assertSee($exam->position_1)
            ->assertTableActionEnabled('Conduct', $exam);
    }

    #[Test]
    public function test_redirects_to_conduct_page_when_conducting_exam()
    {
        $this->panelUser->givePermissionTo('training.exams.access');

        $student = Account::factory()->create();
        Member::factory()->create(['id' => $student->id, 'cid' => $student->id]);

        $exam = ExamBooking::factory()->create(['taken' => 1, 'finished' => ExamBooking::NOT_FINISHED_FLAG, 'student_id' => $student->id]);
        $exam->examiners()->create([
            'examid' => $exam->id,
            'senior' => $this->panelUser->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(Exams::class)
            ->assertTableActionHasUrl(name: 'Conduct', record: $exam, url: ConductExam::getUrl(['examId' => $exam->id]))
            ->callTableAction('Conduct', record: $exam);
    }
}
