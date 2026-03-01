<?php

namespace Tests\Feature\TrainingPanel\Exams;

use App\Filament\Training\Pages\TheoryExam\TheoryExamQuestions;
use App\Models\Cts\Member;
use App\Models\Cts\TheoryQuestion;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class TheoryExamQuestionsTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected array $theoryQuestions = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Create theory exam data for all levels
        $this->createTheoryExamData();
    }

    private function createTheoryExamData(): void
    {
        $examLevels = ['S1', 'S2', 'S3', 'C1'];

        foreach ($examLevels as $level) {
            // Create a student account and member
            $student = Account::factory()->create();
            $studentMember = Member::factory()->create([
                // emulate different internal CID ID to check relationship mapping
                'id' => $student->generateCTSInternalID($student->id),
                'cid' => $student->id,
            ]);

            // Create a theory result
            $theoryQuestion = TheoryQuestion::factory()->create();

            $this->theoryQuestions[$level] = $theoryQuestion;
        }
    }

    #[Test]
    public function it_loads_if_user_has_basic_access()
    {
        $this->panelUser->givePermissionTo('training.theory.access');

        Livewire::actingAs($this->panelUser)
            ->test(TheoryExamQuestions::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_does_not_load_if_user_lacks_basic_access()
    {
        Livewire::actingAs($this->panelUser)
            ->test(TheoryExamQuestions::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_only_shows_levels_user_is_allowed_to_manage()
    {
        $this->panelUser->givePermissionTo(['training.theory.access', 'training.theory.manage.obs', 'training.theory.manage.ctr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamQuestions::class)
            ->assertSuccessful();

        $component->assertSee('S1');
        $component->assertDontSee('S2');
        $component->assertDontSee('S3');
        $component->assertSee('C1');
    }

    #[Test]
    public function it_filters_questions_by_selected_level()
    {
        $this->panelUser->givePermissionTo(['training.theory.access', 'training.theory.manage.obs', 'training.theory.manage.twr']);

        $s1 = TheoryQuestion::factory()->create(['level' => 'S1']);
        $s2 = TheoryQuestion::factory()->create(['level' => 'S2']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamQuestions::class, ['level' => 'S1'])
            ->assertSuccessful();

        $component->assertSee($s1->question);
        $component->assertDontSee($s2->question);
    }

    #[Test]
    public function it_does_not_show_deleted_questions()
    {
        $this->panelUser->givePermissionTo(['training.theory.access', 'training.theory.manage.obs']);

        $question = TheoryQuestion::factory()->create(['level' => 'S1', 'deleted' => 1]);

        Livewire::actingAs($this->panelUser)
            ->test(TheoryExamQuestions::class, ['level' => 'S1'])
            ->assertSuccessful()
            ->assertDontSee($question->question);
    }

    #[Test]
    public function it_can_create_a_new_question()
    {
        $this->panelUser->givePermissionTo(['training.theory.access', 'training.theory.manage.obs']);

        Livewire::actingAs($this->panelUser)
            ->test(TheoryExamQuestions::class, ['level' => 'S1'])
            ->assertSuccessful()
            ->callAction('create', [
                'level' => 'S1',
                'question' => 'What is the capital of France?',
                'option_1' => 'Berlin',
                'option_2' => 'Madrid',
                'option_3' => 'Paris',
                'option_4' => 'Rome',
                'answer' => 3,
            ])
            ->assertHasNoErrors();

        $this->assertDatabaseHas('theory_questions', [
            'level' => 'S1',
            'question' => 'What is the capital of France?',
            'option_1' => 'Berlin',
            'option_2' => 'Madrid',
            'option_3' => 'Paris',
            'option_4' => 'Rome',
            'answer' => 3,
            'add_by' => $this->panelUser->id,
        ], connection: 'cts');
    }

    #[Test]
    public function it_cannot_create_a_new_question_without_permission()
    {
        $this->panelUser->givePermissionTo('training.theory.access', 'training.theory.manage.app');

        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamQuestions::class, ['level' => 'S3'])
            ->assertSuccessful();

        $component->callAction('create', [
            'level' => 'S1',
            'question' => 'What is 9+10?',
            'option_1' => '20',
            'option_2' => '19',
            'option_3' => '21',
            'option_4' => '57',
            'answer' => 2,
        ]);

        $this->assertDatabaseMissing('theory_questions', [
            'level' => 'S1',
            'question' => 'What is 9+10?',
            'option_1' => '20',
            'option_2' => '19',
            'option_3' => '21',
            'option_4' => '57',
            'answer' => 2,
            'status' => true,
        ], connection: 'cts');
    }

    #[Test]
    public function it_can_create_a_new_question_with_permission()
    {
        $this->panelUser->givePermissionTo('training.theory.access', 'training.theory.manage.app');

        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamQuestions::class, ['level' => 'S3'])
            ->assertSuccessful();

        $component->callAction('create', [
            'level' => 'S3',
            'question' => 'What is 9+10?',
            'option_1' => '20',
            'option_2' => '19',
            'option_3' => '21',
            'option_4' => '57',
            'answer' => 2,
            'status' => true,
        ]);

        $this->assertDatabaseHas('theory_questions', [
            'level' => 'S3',
            'question' => 'What is 9+10?',
            'option_1' => '20',
            'option_2' => '19',
            'option_3' => '21',
            'option_4' => '57',
            'answer' => 2,
            'status' => true,
        ], connection: 'cts');
    }

    #[Test]
    public function it_can_edit_a_question_with_permission()
    {
        $this->panelUser->givePermissionTo(['training.theory.access', 'training.theory.manage.obs']);

        $question = TheoryQuestion::factory()->create(['level' => 'S1', 'question' => 'old question']);

        Livewire::actingAs($this->panelUser)
            ->test(TheoryExamQuestions::class, ['level' => 'S1'])
            ->assertSuccessful()
            ->mountTableAction('edit', $question)
            ->setTableActionData([
                'level' => 'S1',
                'question' => 'Updated question',
                'option_1' => $question->option_1,
                'option_2' => $question->option_2,
                'option_3' => $question->option_3,
                'option_4' => $question->option_4,
                'answer' => $question->answer,
                'status' => $question->status,
            ])->callMountedTableAction();

        $this->assertDatabaseHas('theory_questions', [
            'id' => $question->id,
            'question' => 'Updated question',
        ], connection: 'cts');
    }

    #[Test]
    public function it_can_delete_a_question_with_permission()
    {
        $this->panelUser->givePermissionTo(['training.theory.access', 'training.theory.manage.obs']);

        $question = TheoryQuestion::factory()->create(['level' => 'S1']);

        Livewire::actingAs($this->panelUser)
            ->test(TheoryExamQuestions::class, ['level' => 'S1'])
            ->assertSuccessful()
            ->callTableAction('delete', $question);

        $this->assertDatabaseHas('theory_questions', [
            'id' => $question->id,
            'deleted' => 1,
        ], connection: 'cts');
    }

    #[Test]
    public function it_cannot_delete_a_question_without_permission()
    {
        $this->panelUser->givePermissionTo('training.theory.access', 'training.theory.manage.app');

        $question = TheoryQuestion::factory()->create(['level' => 'S1']);

        Livewire::actingAs($this->panelUser)
            ->test(TheoryExamQuestions::class, ['level' => 'S1'])
            ->assertSuccessful()
            ->callTableAction('delete', $question);

        $this->assertDatabaseHas('theory_questions', [
            'id' => $question->id,
            'deleted' => 0,
        ], connection: 'cts');
    }
}
