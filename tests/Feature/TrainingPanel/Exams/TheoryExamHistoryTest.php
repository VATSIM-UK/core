<?php

namespace Tests\Feature\TrainingPanel\Exams;

use App\Filament\Training\Pages\TheoryExamHistory;
use App\Models\Cts\Member;
use App\Models\Cts\TheoryResult;
use App\Models\Mship\Account;
use App\Models\Cts\TheoryQuestion;
use App\Models\Cts\TheoryAnswer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class TheoryExamHistoryTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected array $theoryResults = [];

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
                'id' => $student->id,
                'cid' => $student->id,
            ]);

            // Create a theory result
            $theoryResult = TheoryResult::factory()->create([
                'student_id' => $studentMember->id,
                'exam' => $level,
                'pass' => 1,
                'submitted_time' => now()->subDays(rand(1, 30)),
            ]);

            $this->theoryResults[$level] = $theoryResult;
        }
    }

    #[Test]
    public function it_loads_if_user_has_basic_access()
    {
        $this->panelUser->givePermissionTo('training.theory.access');

        Livewire::actingAs($this->panelUser)
            ->test(TheoryExamHistory::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_does_not_load_if_user_lacks_basic_access()
    {
        Livewire::actingAs($this->panelUser)
            ->test(TheoryExamHistory::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_shows_no_exams_when_user_has_no_view_permissions()
    {
        $this->panelUser->givePermissionTo('training.theory.access');

        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamHistory::class)
            ->assertSuccessful();

        // Should show no table data since user has no conduct permissions
        $component->assertDontSee($this->theoryResults['S1']->student->account->name);
        $component->assertDontSee($this->theoryResults['S2']->student->account->name);
        $component->assertDontSee($this->theoryResults['S3']->student->account->name);
        $component->assertDontSee($this->theoryResults['C1']->student->account->name);
    }

    #[Test]
    public function it_shows_only_s1_exams_when_user_has_s1_permission()
    {
        $this->panelUser->givePermissionTo(['training.theory.access', 'training.theory.view.obs']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamHistory::class)
            ->assertSuccessful();

        // Should show S1 exams
        $component->assertSee($this->theoryResults['S1']->student->account->name);
        $component->assertSee('S1');

        // Should not show other exam types
        $component->assertDontSee($this->theoryResults['S2']->student->account->name);
        $component->assertDontSee($this->theoryResults['S3']->student->account->name);
        $component->assertDontSee($this->theoryResults['C1']->student->account->name);
    }

    #[Test]
    public function it_shows_only_twr_exams_when_user_has_twr_permission()
    {
        $this->panelUser->givePermissionTo(['training.theory.access', 'training.theory.view.twr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamHistory::class)
            ->assertSuccessful();

        // Should show TWR exams
        $component->assertSee($this->theoryResults['S2']->student->account->name);
        $component->assertSee('S2');

        // Should not show other exam types
        $component->assertDontSee($this->theoryResults['S1']->student->account->name);
        $component->assertDontSee($this->theoryResults['S3']->student->account->name);
        $component->assertDontSee($this->theoryResults['C1']->student->account->name);
    }

    #[Test]
    public function it_shows_only_app_exams_when_user_has_app_permission()
    {
        $this->panelUser->givePermissionTo(['training.theory.access', 'training.theory.view.app']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamHistory::class)
            ->assertSuccessful();

        // Should show APP exams
        $component->assertSee($this->theoryResults['S3']->student->account->name);
        $component->assertSee('S3');

        // Should not show other exam types
        $component->assertDontSee($this->theoryResults['S1']->student->account->name);
        $component->assertDontSee($this->theoryResults['S2']->student->account->name);
        $component->assertDontSee($this->theoryResults['C1']->student->account->name);
    }

    #[Test]
    public function it_shows_only_ctr_exams_when_user_has_ctr_permission()
    {
        $this->panelUser->givePermissionTo(['training.theory.access', 'training.theory.view.ctr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamHistory::class)
            ->assertSuccessful();

        // Should show CTR exams
        $component->assertSee($this->theoryResults['C1']->student->account->name);
        $component->assertSee('C1');

        // Should not show other exam types
        $component->assertDontSee($this->theoryResults['S1']->student->account->name);
        $component->assertDontSee($this->theoryResults['S2']->student->account->name);
        $component->assertDontSee($this->theoryResults['S3']->student->account->name);
    }

    #[Test]
    public function it_shows_multiple_exam_types_when_user_has_multiple_permissions()
    {
        $this->panelUser->givePermissionTo([
            'training.theory.access',
            'training.theory.view.obs',
            'training.theory.view.twr',
        ]);

        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamHistory::class)
            ->assertSuccessful();

        // Should show S1 and TWR exams
        $component->assertSee($this->theoryResults['S1']->student->account->name);
        $component->assertSee($this->theoryResults['S2']->student->account->name);
        $component->assertSee('S1');
        $component->assertSee('S2');

        // Should not show APP and CTR exams
        $component->assertDontSee($this->theoryResults['S3']->student->account->name);
        $component->assertDontSee($this->theoryResults['C1']->student->account->name);
    }

    #[Test]
    public function it_shows_all_exam_types_when_user_has_all_permissions()
    {
        $this->panelUser->givePermissionTo([
            'training.theory.access',
            'training.theory.view.obs',
            'training.theory.view.twr',
            'training.theory.view.app',
            'training.theory.view.ctr',
        ]);

        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamHistory::class)
            ->assertSuccessful();

        // Should show all exam types
        $component->assertSee($this->theoryResults['S1']->student->account->name);
        $component->assertSee($this->theoryResults['S2']->student->account->name);
        $component->assertSee($this->theoryResults['S3']->student->account->name);
        $component->assertSee($this->theoryResults['C1']->student->account->name);

        $component->assertSee('S1');
        $component->assertSee('S2');
        $component->assertSee('S3');
        $component->assertSee('C1');
    }

    #[Test]
    public function it_displays_correct_exam_result_badges()
    {
        // Update some results to different states
        $this->theoryResults['S1']->update(['pass' => 1]);
        $this->theoryResults['S2']->update(['pass' => 0]);

        $this->panelUser->givePermissionTo([
            'training.theory.access',
            'training.theory.view.obs',
            'training.theory.view.twr',
        ]);

        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamHistory::class)
            ->assertSuccessful();

        // Should show correct result badges
        $component->assertSee('Passed');
        $component->assertSee('Failed');
    }

    #[Test]
    public function it_displays_exam_information_correctly()
    {
        $this->panelUser->givePermissionTo(['training.theory.access', 'training.theory.view.twr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamHistory::class)
            ->assertSuccessful();

        $twr = $this->theoryResults['S2'];

        // Should show exam information
        $component->assertSee($twr->student->account->id); // CID
        $component->assertSee($twr->student->account->name); // Name
        $component->assertSee('S2'); // Exam type
        $component->assertSee($twr->submitted_time); // Submitted Time
    }

    #[Test]
    public function it_has_view_action_for_each_exam()
    {
        $this->panelUser->givePermissionTo(['training.theory.access', 'training.theory.view.twr']);

        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamHistory::class)
            ->assertSuccessful();

        // Should have view action
        $component->assertSee('View');
    }

    #[Test]
    public function it_can_filter_by_cid()
    {
        $this->panelUser->givePermissionTo([
            'training.theory.access',
            'training.theory.view.obs',
            'training.theory.view.twr',
        ]);

        $s1Cid = $this->theoryResults['S1']->student->account->id;
        $twrCid = $this->theoryResults['S2']->student->account->id;

        // Create a filter for the CID
        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamHistory::class)
            ->assertSuccessful()
            ->assertSee($s1Cid)
            ->assertSee($twrCid);
    }

    #[Test]
    public function it_can_filter_by_exam_date_range()
    {
        $this->panelUser->givePermissionTo([
            'training.theory.access',
            'training.theory.view.obs',
            'training.theory.view.twr',
        ]);

        // Set specific dates for exams to test filtering
        $this->theoryResults['S1']->update([
            'submitted_time' => now()->subDays(5)->format('Y-m-d'),
            'exam' => 'S1',
        ]);
        $this->theoryResults['S2']->update([
            'submitted_time' => now()->subDays(20)->format('Y-m-d'),
            'exam' => 'S2',
        ]);

        // Filter for exams in the last 10 days
        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamHistory::class)
            ->assertSuccessful()
            ->filterTable('exam_date', [
                'exam_date_from' => now()->subDays(10)->format('Y-m-d'),
                'exam_date_to' => now()->format('Y-m-d'),
            ]);

        // Should find the S1 exam (within last 10 days)
        $component->assertSee($this->theoryResults['S1']->student->account->name);

        // Should not find the TWR exam (older than 10 days)
        $component->assertDontSee($this->theoryResults['S2']->student->account->name);
    }

    #[Test]
    public function it_can_filter_by_position()
    {
        $this->panelUser->givePermissionTo([
            'training.theory.access',
            'training.theory.view.obs',
            'training.theory.view.twr',
            'training.theory.view.app',
        ]);

        // Update exam positions to use specific prefixes for filtering
        $this->theoryResults['S1']->update(['exam' => 'S1']);
        $this->theoryResults['S2']->update(['exam' => 'S2']);
        $this->theoryResults['S3']->update(['exam' => 'S3']);
        $this->theoryResults['C1']->update(['exam' => 'C1']);

        // Filter for only TWR positions
        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamHistory::class)
            ->assertSuccessful()
            ->filterTable('exam_rating', [
                'exam_rating' => ['S2'],
            ]);

        // Should find the TWR exam
        $component->assertSee($this->theoryResults['S2']->student->account->name);

        // Should not find other exams
        $component->assertDontSee($this->theoryResults['S1']->student->account->name);
        $component->assertDontSee($this->theoryResults['S3']->student->account->name);

        // Reset and filter for multiple positions
        $component->resetTableFilters()
            ->filterTable('exam_rating', [
                'exam_rating' => ['S1', 'S3'],
            ]);

        // Should find S1 and APP exams
        $component->assertSee($this->theoryResults['S1']->student->account->name);
        $component->assertSee($this->theoryResults['S3']->student->account->name);

        // Should not find TWR exam
        $component->assertDontSee($this->theoryResults['S2']->student->account->name);
    }

    #[Test]
    public function it_combines_filters_correctly()
    {
        $this->panelUser->givePermissionTo([
            'training.theory.access',
            'training.theory.view.obs',
            'training.theory.view.twr',
            'training.theory.view.app',
        ]);

        // Set specific dates for exams to test filtering
        $this->theoryResults['S1']->update([
            'submitted_time' => now()->subDays(5)->format('Y-m-d'),
            'exam' => 'S1',
        ]);
        $this->theoryResults['S2']->update([
            'submitted_time' => now()->subDays(10)->format('Y-m-d'),
            'exam' => 'S2',
        ]);
        $this->theoryResults['S3']->update([
            'submitted_time' => now()->subDays(15)->format('Y-m-d'),
            'exam' => 'S3',
        ]);

        // Filter for S1 and TWR exams in the last 14 days
        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamHistory::class)
            ->assertSuccessful()
            ->filterTable('exam_date', [
                'exam_date_from' => now()->subDays(14)->format('Y-m-d'),
                'exam_date_to' => now()->format('Y-m-d'),
            ])
            ->filterTable('exam_rating', [
                'exam_rating' => ['S1', 'S2', 'S3'],
            ]);

        // Should find S1 and TWR exams (within last 14 days)
        $component->assertSee($this->theoryResults['S1']->student->account->name);
        $component->assertSee($this->theoryResults['S2']->student->account->name);

        // Should not find APP exam (older than 14 days)
        $component->assertDontSee($this->theoryResults['S3']->student->account->name);
    }

    #[Test]
    public function it_shows_questions_and_answers_for_a_theory_result()
    {
        $this->panelUser->givePermissionTo(['training.theory.access', 'training.theory.view.app']);

        $result = $this->theoryResults['S3'];

        $question = TheoryQuestion::factory()->create([
            'question' => 'What is the correct ATC phrase for climb?',
            'option_1' => 'Climb flight level 130',
            'option_2' => 'Go up',
            'option_3' => 'Increase altitude',
            'option_4' => 'Ascend please',
        ]);

        TheoryAnswer::factory()->create([
            'theory_id' => $result->id,
            'question_id' => $question->id,
            'answer_given' => 1,
        ]);

        $component = Livewire::actingAs($this->panelUser)
            ->test(TheoryExamHistory::class)
            ->mountTableAction('view', $result)
            ->assertSee('What is the correct ATC phrase for climb?')
            ->assertSee('Climb flight level 130');
    }
}
