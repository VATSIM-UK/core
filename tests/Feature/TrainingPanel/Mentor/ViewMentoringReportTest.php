<?php

namespace Tests\Feature\TrainingPanel\Mentor;

use App\Enums\FieldScore;
use App\Filament\Training\Pages\Mentor\ViewMentoringReport;
use App\Models\Cts\Member;
use App\Models\Cts\ProgSheet;
use App\Models\Cts\ProgSheetCategory;
use App\Models\Cts\ProgSheetField;
use App\Models\Cts\ReportSheet;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\MentorPermissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ViewMentoringReportTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected Account $student;

    protected Member $studentMember;

    protected Account $mentor;

    protected Member $mentorMember;

    protected Session $mentoringSession;

    protected ProgSheet $progSheet;

    protected ProgSheetCategory $category;

    protected ProgSheetField $field;

    protected ReportSheet $reportSheet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = Account::factory()->create();
        $this->studentMember = Member::factory()->create([
            'id' => $this->student->id,
            'cid' => $this->student->id,
        ]);

        $this->mentor = Account::factory()->create();
        $this->mentorMember = Member::factory()->create([
            'id' => $this->mentor->id,
            'cid' => $this->mentor->id,
        ]);

        $this->mentoringSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'progress_sheet_id' => 0,
            'taken_date' => '2025-03-15',
            'taken_from' => '18:00',
            'taken_to' => '20:00',
            'filed' => now(),
        ]);

        $this->progSheet = ProgSheet::factory()->create();

        $this->category = ProgSheetCategory::factory()
            ->forProgSheet($this->progSheet->prog_sheet_id)
            ->create(['catName' => 'General Control']);

        $this->field = ProgSheetField::factory()
            ->forProgSheet($this->progSheet->prog_sheet_id)
            ->forCategory($this->category->catId)
            ->create(['field' => 'Radio Telephony']);

        $this->reportSheet = ReportSheet::factory()
            ->forSession($this->mentoringSession->id)
            ->forStudent($this->studentMember->id)
            ->forField($this->field->field_id)
            ->create([
                'prog_sheet_id' => $this->progSheet->prog_sheet_id,
                'field_score' => FieldScore::TEST_STANDARD->value,
                'notes' => 'Excellent RT throughout the session.',
            ]);

        $this->mentoringSession->update(['progress_sheet_id' => $this->progSheet->prog_sheet_id]);
    }

    #[Test]
    public function it_loads_successfully_for_the_student(): void
    {
        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSuccessful();
    }

    #[Test]
    public function it_loads_successfully_for_the_mentor_who_conducted_the_session(): void
    {
        Livewire::actingAs($this->mentor)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSuccessful();
    }

    #[Test]
    public function it_loads_for_a_user_with_a_mentor_training_position_for_the_session_position(): void
    {
        $authorisedMentor = Account::factory()->create();

        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => ['EGLL_APP'],
        ]);

        MentorTrainingPosition::create([
            'account_id' => $authorisedMentor->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $trainingPosition->id,
            'created_by' => $authorisedMentor->id,
        ]);

        Livewire::actingAs($authorisedMentor)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSuccessful();
    }

    #[Test]
    public function it_denies_an_entirely_unrelated_user(): void
    {
        $unrelatedUser = Account::factory()->create();

        $this->mock(MentorPermissionService::class, fn ($mock) => $mock->shouldReceive('getCtsCallsignsForMentorable')->andReturn([]));

        Livewire::actingAs($unrelatedUser)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertForbidden();
    }

    #[Test]
    public function it_denies_a_different_student_viewing_another_students_session(): void
    {
        $otherStudent = Account::factory()->create();
        Member::factory()->create([
            'id' => $otherStudent->id,
            'cid' => $otherStudent->id,
        ]);

        $this->mock(MentorPermissionService::class, fn ($mock) => $mock->shouldReceive('getCtsCallsignsForMentorable')->andReturn([]));

        Livewire::actingAs($otherStudent)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertForbidden();
    }

    #[Test]
    public function it_denies_a_mentor_who_did_not_conduct_the_session_and_lacks_position_permission(): void
    {
        $otherMentor = Account::factory()->create();
        Member::factory()->create([
            'id' => $otherMentor->id,
            'cid' => $otherMentor->id,
        ]);

        $this->mock(MentorPermissionService::class, fn ($mock) => $mock->shouldReceive('getCtsCallsignsForMentorable')->andReturn([]));

        Livewire::actingAs($otherMentor)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertForbidden();
    }

    #[Test]
    public function test_displays_student_name(): void
    {
        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSee($this->student->name);
    }

    #[Test]
    public function test_displays_student_cid(): void
    {
        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSee((string) $this->student->id);
    }

    #[Test]
    public function test_displays_mentor_name(): void
    {
        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSee($this->mentor->name);
    }

    #[Test]
    public function test_displays_mentor_cid(): void
    {
        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSee((string) $this->mentor->id);
    }

    #[Test]
    public function test_displays_the_session_position(): void
    {
        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSee('EGLL_APP');
    }

    #[Test]
    public function test_additional_comments_section_is_visible_when_comments_field_exists(): void
    {
        ReportSheet::factory()
            ->forSession($this->mentoringSession->id)
            ->forStudent($this->studentMember->id)
            ->create([
                'field_id' => 0,
                'prog_sheet_id' => $this->progSheet->prog_sheet_id,
                'notes' => '<p>Great session overall.</p>',
            ]);

        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSee('Additional Comments');
    }

    #[Test]
    public function test_additional_comments_section_is_hidden_when_no_comments_field_exists(): void
    {
        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertDontSee('Additional Comments');
    }

    #[Test]
    public function test_renders_html_content_in_additional_comments(): void
    {
        ReportSheet::factory()
            ->forSession($this->mentoringSession->id)
            ->forStudent($this->studentMember->id)
            ->create([
                'field_id' => 0,
                'prog_sheet_id' => $this->progSheet->prog_sheet_id,
                'notes' => '<p>Well done on <strong>vectors</strong> today.</p>',
            ]);

        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSee('vectors', false);
    }

    #[Test]
    public function test_displays_category_name_as_section_header(): void
    {
        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSee('General Control');
    }

    #[Test]
    public function test_displays_field_name_within_category(): void
    {
        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSee('Radio Telephony');
    }

    #[Test]
    public function test_displays_field_score_badge_label(): void
    {
        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSee(FieldScore::TEST_STANDARD->getLabel());
    }

    #[Test]
    public function test_displays_field_notes_when_present(): void
    {
        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSee('Excellent RT throughout the session.');
    }

    #[Test]
    public function test_hides_field_notes_entry_when_notes_are_blank(): void
    {
        $this->reportSheet->update(['notes' => '']);

        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSuccessful()
            ->assertDontSee('Excellent RT throughout the session.');
    }

    #[Test]
    public function test_displays_multiple_categories_as_separate_sections(): void
    {
        $category2 = ProgSheetCategory::factory()
            ->forProgSheet($this->progSheet->prog_sheet_id)
            ->create(['catName' => 'Separation Standards']);

        $field2 = ProgSheetField::factory()
            ->forProgSheet($this->progSheet->prog_sheet_id)
            ->forCategory($category2->catId)
            ->create(['field' => 'Horizontal Separation']);

        ReportSheet::factory()
            ->forSession($this->mentoringSession->id)
            ->forStudent($this->studentMember->id)
            ->forField($field2->field_id)
            ->create([
                'prog_sheet_id' => $this->progSheet->prog_sheet_id,
                'field_score' => FieldScore::GOOD->value,
                'notes' => '',
            ]);

        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSee('General Control')
            ->assertSee('Separation Standards')
            ->assertSee('Horizontal Separation');
    }

    #[Test]
    public function test_displays_multiple_fields_within_the_same_category(): void
    {
        $field2 = ProgSheetField::factory()
            ->forProgSheet($this->progSheet->prog_sheet_id)
            ->forCategory($this->category->catId)
            ->create(['field' => 'Phraseology']);

        ReportSheet::factory()
            ->forSession($this->mentoringSession->id)
            ->forStudent($this->studentMember->id)
            ->forField($field2->field_id)
            ->create([
                'prog_sheet_id' => $this->progSheet->prog_sheet_id,
                'field_score' => FieldScore::DEVELOPING->value,
                'notes' => 'Needs improvement on phraseology.',
            ]);

        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSee('Radio Telephony')
            ->assertSee('Phraseology')
            ->assertSee('Needs improvement on phraseology.');
    }

    public static function fieldScoreProvider(): array
    {
        return [
            'not applicable' => [FieldScore::NOT_APPLICABLE],
            'covered' => [FieldScore::COVERED],
            'developing' => [FieldScore::DEVELOPING],
            'good' => [FieldScore::GOOD],
            'test standard' => [FieldScore::TEST_STANDARD],
        ];
    }

    #[Test]
    #[DataProvider('fieldScoreProvider')]
    public function test_displays_correct_label_for_each_field_score(FieldScore $score): void
    {
        $this->reportSheet->update(['field_score' => $score->value]);

        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSee($score->getLabel());
    }

    #[Test]
    public function test_shows_previous_sessions_for_the_same_student_and_position(): void
    {
        $previousSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'progress_sheet_id' => $this->progSheet->prog_sheet_id,
            'taken_date' => '2025-01-10',
            'filed' => now(),
        ]);

        $component = Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id]);

        $this->assertTrue(
            $component->instance()->otherSessions->contains('id', $previousSession->id)
        );
    }

    #[Test]
    public function test_previous_sessions_excludes_sessions_for_a_different_position(): void
    {
        $differentPositionSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_TWR',
            'taken_date' => '2025-01-05',
            'filed' => now(),
        ]);

        $component = Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id]);

        $this->assertFalse(
            $component->instance()->otherSessions->contains('id', $differentPositionSession->id)
        );
    }

    #[Test]
    public function test_previous_sessions_excludes_sessions_for_a_different_student(): void
    {
        $otherStudent = Member::factory()->create();

        $otherStudentSession = Session::factory()->create([
            'student_id' => $otherStudent->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'taken_date' => '2025-01-08',
            'filed' => now(),
        ]);

        $component = Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id]);

        $this->assertFalse(
            $component->instance()->otherSessions->contains('id', $otherStudentSession->id)
        );
    }

    #[Test]
    public function test_previous_sessions_are_ordered_most_recent_first(): void
    {
        $older = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'progress_sheet_id' => $this->progSheet->prog_sheet_id,
            'taken_date' => '2024-11-01',
            'filed' => now(),
        ]);

        $newer = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'progress_sheet_id' => $this->progSheet->prog_sheet_id,
            'taken_date' => '2025-02-01',
            'filed' => now(),
        ]);

        $component = Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id]);

        $sessions = $component->instance()->otherSessions;
        $this->assertSame($newer->id, $sessions->first()->id);
        $this->assertSame($older->id, $sessions->last()->id);
    }

    #[Test]
    public function test_page_renders_other_sessions_section(): void
    {
        Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'progress_sheet_id' => $this->progSheet->prog_sheet_id,
            'taken_date' => '2025-01-20',
            'filed' => now(),
        ]);

        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSee('Other Sessions');
    }

    #[Test]
    public function test_all_sessions_collection_includes_the_current_session(): void
    {
        $component = Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id]);

        $this->assertTrue(
            $component->instance()->allSessions->contains('id', $this->mentoringSession->id)
        );
    }

    #[Test]
    public function test_all_sessions_excludes_sessions_on_a_different_progress_sheet(): void
    {
        $differentProgSheet = ProgSheet::factory()->create();

        $oldSheetSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'taken_date' => '2024-06-01',
            'progress_sheet_id' => $differentProgSheet->prog_sheet_id,
            'filed' => now(),
        ]);

        $component = Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id]);

        $this->assertFalse($component->instance()->allSessions->contains('id', $oldSheetSession->id));
    }

    #[Test]
    public function test_sessions_overview_action_is_registered_on_the_page(): void
    {
        $component = Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id]);

        $component->callAction('viewSessionsOverview')
            ->assertSuccessful();
    }

    #[Test]
    public function test_by_session_tab_includes_all_sessions_for_position(): void
    {
        Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'progress_sheet_id' => $this->progSheet->prog_sheet_id,
            'taken_date' => '2025-01-05',
            'filed' => now(),
        ]);

        $component = Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id]);

        $sections = $component->instance()->getSessionsBySessionTab();
        $headings = array_map(fn ($s) => $s->getHeading(), $sections);

        $this->assertContains('15/03/2025', $headings);
        $this->assertContains('05/01/2025', $headings);
    }

    #[Test]
    public function test_by_criteria_tab_shows_field_name(): void
    {
        $component = Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id]);

        $sections = $component->instance()->getSessionsByCriteriaTab();

        $this->assertNotEmpty($sections);
    }

    #[Test]
    public function test_by_criteria_tab_returns_empty_state_when_no_sessions_have_report_sheets(): void
    {
        $newStudent = Account::factory()->create();
        $newMember = Member::factory()->create([
            'id' => $newStudent->id,
            'cid' => $newStudent->id,
        ]);

        $bareSession = Session::factory()->create([
            'student_id' => $newMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'taken_date' => '2025-05-01',
            'filed' => now(),
        ]);

        Livewire::actingAs($newStudent)
            ->test(ViewMentoringReport::class, ['sessionId' => $bareSession->id])
            ->assertSee('No report data found for this session.');
    }
}
