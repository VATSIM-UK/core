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
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
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
            'taken_date' => now()->format('Y-m-d'),
            'taken_from' => '18:00',
            'taken_to' => '20:00',
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
    }

    #[Test]
    public function it_loads_successfully_for_the_student()
    {
        $this->withoutExceptionHandling();

        Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSuccessful();
    }

    #[Test]
    public function it_loads_successfully_for_the_mentor()
    {
        Livewire::actingAs($this->mentor)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSuccessful();
    }

    #[Test]
    public function test_displays_student_and_mentor_information_correctly()
    {
        $component = Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSuccessful();

        $component->assertSee($this->student->name)
            ->assertSee($this->student->id)
            ->assertSee($this->mentor->name)
            ->assertSee($this->mentor->id);
    }

    #[Test]
    public function test_displays_session_information_correctly()
    {
        $component = Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSuccessful();

        $component->assertSee('EGLL_APP')
            ->assertSee('18:00')
            ->assertSee('20:00');
    }

    #[Test]
    public function test_displays_report_sheets_grouped_by_category()
    {
        $component = Livewire::actingAs($this->student)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->mentoringSession->id])
            ->assertSuccessful();

        $component->assertSee('General Control')
            ->assertSee('Radio Telephony')
            ->assertSee(FieldScore::TEST_STANDARD->getLabel())
            ->assertSee('Excellent RT throughout the session.');
    }
}
