<?php

namespace Tests\Feature\TrainingPanel\MyTraining;

use App\Filament\Training\Pages\Exam\ViewExamReport;
use App\Filament\Training\Pages\MyTraining\Widgets\MyPracticalExamHistoryTable;
use App\Filament\Training\Pages\MyTraining\Widgets\MyTheoryExamHistoryTable;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalResult;
use App\Models\Cts\TheoryAnswer;
use App\Models\Cts\TheoryQuestion;
use App\Models\Cts\TheoryResult;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class MyExamHistoryTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected Account $studentAccount;

    protected Member $studentMember;

    protected ExamBooking $examBooking;

    protected PracticalResult $practicalResult;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentAccount = Account::factory()->create();
        $this->studentMember = Member::factory()->recycle($this->studentAccount)->create([
            'cid' => $this->studentAccount->id,
        ]);

        $this->examBooking = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::FINISHED_FLAG,
            'exam' => 'TWR',
            'student_id' => $this->studentMember->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
            'position_1' => 'EGKK_TWR',
        ]);
        $this->examBooking->examiners()->create([
            'examid' => $this->examBooking->id,
            'senior' => $this->panelUser->id,
        ]);

        $this->practicalResult = PracticalResult::factory()->create([
            'examid' => $this->examBooking->id,
            'student_id' => $this->studentMember->id,
            'result' => PracticalResult::PASSED,
            'exam' => 'TWR',
            'date' => now()->subDays(5),
        ]);

        $this->theoryResult = TheoryResult::factory()->create([
            'student_id' => $this->studentMember->id,
            'exam' => 'S2',
            'correct' => 2,
            'questions' => 2,
            'pass' => 1,
            'submitted' => 1,
            'submitted_time' => now()->subDays(10),
        ]);

        $question = TheoryQuestion::factory()->create([
            'level' => 'S2',
        ]);

        TheoryAnswer::factory()->create([
            'theory_id' => $this->theoryResult->id,
            'question_id' => $question->id,
            'answer_given' => $question->answer,
        ]);

        $this->studentAccount->givePermissionTo('training.access');
    }

    #[Test]
    public function it_has_a_view_report_action_on_each_row_of_practical_exams_widget(): void
    {
        Livewire::actingAs($this->studentAccount)
            ->test(MyPracticalExamHistoryTable::class)
            ->assertSuccessful()
            ->assertSee('View Report');

    }

    #[Test]
    public function view_report_action_links_to_the_correct_exam_report_url_on_practical_exams_widget(): void
    {
        $expectedUrl = ViewExamReport::getUrl(['examId' => $this->practicalResult->examid]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyPracticalExamHistoryTable::class)
            ->assertSuccessful()
            ->assertSee($expectedUrl);
    }

    #[Test]
    public function it_only_shows_results_belonging_to_the_authenticated_member_on_practical_exams_widget(): void
    {
        $otherAccount = Account::factory()->create();
        $otherMember = Member::factory()->recycle($otherAccount)->create([
            'cid' => $otherAccount->id,
        ]);
        $otherBooking = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::FINISHED_FLAG,
            'exam' => 'APP',
            'student_id' => $otherMember->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
            'position_1' => 'EGKK_APP',
        ]);
        $otherBooking->examiners()->create([
            'examid' => $otherBooking->id,
            'senior' => $this->panelUser->id,
        ]);
        PracticalResult::factory()->create([
            'examid' => $otherBooking->id,
            'student_id' => $otherMember->id,
            'result' => PracticalResult::FAILED,
            'exam' => 'APP',
            'date' => now(),
        ]);

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyPracticalExamHistoryTable::class)
            ->assertSuccessful();

        $records = $component->instance()->getTable()->getRecords();

        $this->assertCount(1, $records);
        $this->assertEquals($this->practicalResult->id, $records->first()->id);
    }

    #[Test]
    public function it_shows_multiple_results_when_member_has_sat_multiple_exams_on_practical_exams_widget(): void
    {
        $secondBooking = ExamBooking::factory()->create([
            'taken' => 1,
            'finished' => ExamBooking::FINISHED_FLAG,
            'exam' => 'APP',
            'student_id' => $this->studentMember->id,
            'student_rating' => Qualification::code('S1')->first()->vatsim,
            'position_1' => 'EGKK_APP',
        ]);
        $secondBooking->examiners()->create([
            'examid' => $secondBooking->id,
            'senior' => $this->panelUser->id,
        ]);
        PracticalResult::factory()->create([
            'examid' => $secondBooking->id,
            'student_id' => $this->studentMember->id,
            'result' => PracticalResult::PASSED,
            'exam' => 'APP',
            'date' => now()->subDays(2),
        ]);

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyPracticalExamHistoryTable::class)
            ->assertSuccessful();

        $this->assertCount(2, $component->instance()->getTable()->getRecords());
    }

    #[Test]
    public function it_shows_an_empty_table_when_member_has_no_results_on_practical_exams_widget(): void
    {
        $emptyAccount = Account::factory()->create();
        Member::factory()->recycle($emptyAccount)->create(['cid' => $emptyAccount->id]);
        $emptyAccount->givePermissionTo('training.access');

        $component = Livewire::actingAs($emptyAccount)
            ->test(MyPracticalExamHistoryTable::class)
            ->assertSuccessful();

        $this->assertCount(0, $component->instance()->getTable()->getRecords());
    }

    #[Test]
    public function it_has_a_view_action_on_each_row_of_theory_exams_widget(): void
    {
        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyTheoryExamHistoryTable::class)
            ->assertSuccessful()
            ->assertSee('View');
    }

    #[Test]
    public function it_shows_an_empty_table_when_member_has_no_results_on_theory_exams_widget(): void
    {
        $emptyAccount = Account::factory()->create();
        Member::factory()->recycle($emptyAccount)->create(['cid' => $emptyAccount->id]);
        $emptyAccount->givePermissionTo('training.access');

        $component = Livewire::actingAs($emptyAccount)
            ->test(MyTheoryExamHistoryTable::class)
            ->assertSuccessful();

        $this->assertCount(0, $component->instance()->getTable()->getRecords());
    }

    #[Test]
    public function it_only_shows_results_belonging_to_the_authenticated_member_on_theory_exams_widget(): void
    {
        $otherAccount = Account::factory()->create();
        $otherMember = Member::factory()->recycle($otherAccount)->create([
            'cid' => $otherAccount->id,
        ]);

        TheoryResult::factory()->create([
            'student_id' => $otherMember->id,
            'exam' => 'S2',
            'correct' => 1,
            'questions' => 2,
            'pass' => 0,
            'submitted' => 1,
        ]);

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyTheoryExamHistoryTable::class)
            ->assertSuccessful();

        $this->assertCount(1, $component->instance()->getTable()->getRecords());
        $this->assertEquals($this->theoryResult->id, $component->instance()->getTable()->getRecords()->first()->id);
    }

    #[Test]
    public function it_shows_multiple_results_when_member_has_sat_multiple_exams_on_theory_exams_widget(): void
    {
        $secondTheory = TheoryResult::factory()->create([
            'student_id' => $this->studentMember->id,
            'exam' => 'S1',
            'correct' => 2,
            'questions' => 2,
            'pass' => 1,
            'submitted' => 1,
        ]);

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyTheoryExamHistoryTable::class)
            ->assertSuccessful();

        $this->assertCount(2, $component->instance()->getTable()->getRecords());
    }
}
