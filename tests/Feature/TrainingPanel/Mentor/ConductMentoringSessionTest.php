<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\Mentor;

use App\Enums\FieldScore;
use App\Filament\Training\Pages\Mentor\ConductMentoringSession;
use App\Livewire\Training\AcceptedMentoringSessionsTable;
use App\Models\Cts\Member;
use App\Models\Cts\ProgSheet;
use App\Models\Cts\ProgSheetCategory;
use App\Models\Cts\ProgSheetField;
use App\Models\Cts\ReportSheet;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Notifications\Training\MentoringReportFiled;
use App\Notifications\Training\StudentMentoringNoShow;
use App\Services\Training\MentoringReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class ConductMentoringSessionTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected Account $mentor;

    protected Member $mentorMember;

    protected Account $student;

    protected Member $studentMember;

    protected Session $session;

    protected ProgSheetField $field;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mentor = Account::factory()->create();
        $this->mentorMember = Member::factory()->create([
            'id' => $this->mentor->id,
            'cid' => $this->mentor->id,
        ]);

        $this->student = Account::factory()->create();
        $this->studentMember = Member::factory()->create([
            'id' => $this->student->id,
            'cid' => $this->student->id,
        ]);

        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => ['EGLL_APP'],
            'category' => 'S3 Training',
        ]);

        MentorTrainingPosition::create([
            'account_id' => $this->mentor->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $trainingPosition->id,
            'created_by' => $this->mentor->id,
        ]);

        $progSheet = ProgSheet::factory()->create();
        $category = ProgSheetCategory::factory()
            ->forProgSheet($progSheet->prog_sheet_id)
            ->create(['catName' => 'General Control']);

        $this->field = ProgSheetField::factory()
            ->forProgSheet($progSheet->prog_sheet_id)
            ->forCategory($category->catId)
            ->create(['field' => 'Radio Telephony']);

        $this->session = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'progress_sheet_id' => $progSheet->prog_sheet_id,
            'taken' => 1,
            'taken_date' => now()->subHour()->format('Y-m-d'),
            'taken_from' => now()->subHour()->format('H:i:s'),
            'taken_to' => now()->format('H:i:s'),
            'taken_time' => now()->subDays(3),
            'session_done' => 0,
            'filed' => null,
        ]);
    }

    #[Test]
    public function mentor_can_access_conduct_page_for_their_session(): void
    {
        Livewire::actingAs($this->mentor)
            ->test(ConductMentoringSession::class, ['sessionId' => $this->session->id])
            ->assertSuccessful();
    }

    #[Test]
    public function non_assigned_mentor_cannot_access_conduct_page(): void
    {
        $otherMentor = Account::factory()->create();
        Member::factory()->create(['id' => $otherMentor->id, 'cid' => $otherMentor->id]);

        Livewire::actingAs($otherMentor)
            ->test(ConductMentoringSession::class, ['sessionId' => $this->session->id])
            ->assertForbidden();
    }

    #[Test]
    public function save_persists_criteria_to_report_sheet(): void
    {
        Livewire::actingAs($this->mentor)
            ->test(ConductMentoringSession::class, ['sessionId' => $this->session->id])
            ->set('data.criteria.'.$this->field->field_id.'.score', FieldScore::GOOD->value)
            ->set('data.criteria.'.$this->field->field_id.'.notes', '<p>Strong performance</p>')
            ->call('save');

        $this->assertDatabaseHas('report_sheet', [
            'seshid' => $this->session->id,
            'field_id' => $this->field->field_id,
            'field_score' => FieldScore::GOOD->value,
        ], 'cts');
    }

    #[Test]
    public function submit_files_session_and_notifies_student(): void
    {
        Notification::fake();

        Livewire::actingAs($this->mentor)
            ->test(ConductMentoringSession::class, ['sessionId' => $this->session->id])
            ->set('data.criteria.'.$this->field->field_id.'.score', FieldScore::GOOD->value)
            ->call('submitReport');

        $this->session->refresh();

        $this->assertNotNull($this->session->filed);
        $this->assertSame(1, (int) $this->session->session_done);

        Notification::assertSentTo($this->student, MentoringReportFiled::class);
    }

    #[Test]
    public function submit_is_blocked_when_criteria_are_unscored(): void
    {
        Livewire::actingAs($this->mentor)
            ->test(ConductMentoringSession::class, ['sessionId' => $this->session->id])
            ->set('data.criteria.'.$this->field->field_id.'.score', FieldScore::NOT_SCORED->value)
            ->call('submitReport');

        $this->session->refresh();
        $this->assertNull($this->session->filed);
    }

    #[Test]
    public function mark_no_show_files_session_with_previous_scores(): void
    {
        Notification::fake();

        $previousSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'progress_sheet_id' => $this->session->progress_sheet_id,
            'taken' => 1,
            'taken_date' => now()->subDays(7)->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
            'session_done' => 1,
            'filed' => now()->subDays(7),
        ]);

        ReportSheet::factory()
            ->forSession($previousSession->id)
            ->forStudent($this->studentMember->id)
            ->forField($this->field->field_id)
            ->create([
                'prog_sheet_id' => $this->session->progress_sheet_id,
                'field_score' => FieldScore::DEVELOPING->value,
            ]);

        Carbon::setTestNow(now()->parse($this->session->taken_date.' '.$this->session->taken_from)->addMinutes(6));

        $tgi = Account::factory()->create();
        $tgi->givePermissionTo('training.mentors.manage.atc');

        app(MentoringReportService::class)->markNoShow($this->session->fresh(), false);

        $this->session->refresh();

        $this->assertSame(1, (int) $this->session->noShow);
        $this->assertNotNull($this->session->filed);

        $this->assertDatabaseHas('report_sheet', [
            'seshid' => $this->session->id,
            'field_id' => $this->field->field_id,
            'field_score' => FieldScore::DEVELOPING->value,
        ], 'cts');

        Notification::assertSentTo($tgi, StudentMentoringNoShow::class);

        Carbon::setTestNow();
    }

    #[Test]
    public function short_notice_no_show_without_discord_confirmation_cancels_session(): void
    {
        $this->session->update([
            'taken_time' => now()->subHours(12),
            'taken_date' => now()->addHours(6)->format('Y-m-d'),
            'taken_from' => now()->addHours(6)->format('H:i:s'),
            'taken_to' => now()->addHours(8)->format('H:i:s'),
        ]);

        Carbon::setTestNow(now()->addHours(6)->addMinutes(6));

        app(MentoringReportService::class)->markNoShow($this->session->fresh(), false);

        $this->session->refresh();

        $this->assertSame(0, (int) $this->session->taken);
        $this->assertNull($this->session->mentor_id);
        $this->assertSame(0, (int) $this->session->noShow);

        Carbon::setTestNow();
    }

    #[Test]
    public function accepted_sessions_table_shows_conduct_action(): void
    {
        Livewire::actingAs($this->mentor)
            ->test(AcceptedMentoringSessionsTable::class)
            ->assertTableActionVisible('conduct', $this->session);
    }
}
