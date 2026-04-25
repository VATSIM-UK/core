<?php

namespace Tests\Feature\TrainingPanel\Mentoring;

use App\Filament\Training\Pages\Mentoring\MentoringSessionHistory;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class MentoringSessionHistoryTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    private Member $mentorMember;

    private Member $studentMember;

    protected function connectionsToTransact(): array
    {
        return ['mysql', 'cts'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->mentorMember = Member::firstOrCreate(
            ['id' => $this->panelUser->id],
            [
                'cid' => $this->panelUser->id,
                'joined_div' => now()->toDateString(),
                'old_rts_id' => 0,
            ]
        );

        $student = Account::factory()->create();
        $this->studentMember = Member::firstOrCreate(
            ['id' => $student->id],
            [
                'cid' => $student->id,
                'joined_div' => now()->toDateString(),
                'old_rts_id' => 0,
            ]
        );
    }

    private function grantPositionAccess(array $callsigns, string $category = 'S2 Training'): TrainingPosition
    {
        $trainingPosition = TrainingPosition::factory()->create([
            'category' => $category,
            'cts_positions' => $callsigns,
        ]);

        MentorTrainingPosition::factory()->create([
            'account_id' => $this->panelUser->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $trainingPosition->id,
            'created_by' => $this->panelUser->id,
        ]);

        return $trainingPosition;
    }

    #[Test]
    public function it_loads_if_user_has_at_least_one_mentor_training_position(): void
    {
        $this->grantPositionAccess(['EGPH_APP']);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringSessionHistory::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_is_forbidden_when_user_has_no_mentor_training_positions(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(MentoringSessionHistory::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_shows_sessions_where_the_user_is_the_mentor_regardless_of_position_access(): void
    {
        Session::factory()->create([
            'mentor_id' => $this->mentorMember->id,
            'student_id' => $this->studentMember->id,
            'position' => 'UNKN_TWR',
            'taken_date' => now()->subDay()->toDateString(),
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringSessionHistory::class)
            ->assertSee($this->studentMember->cid);
    }

    #[Test]
    public function it_shows_sessions_by_other_mentors_if_position_is_allowed(): void
    {
        $this->grantPositionAccess(['EGKK_GND']);
        $otherMentor = Member::factory()->create(['joined_div' => now(), 'old_rts_id' => 0]);

        Session::factory()->create([
            'mentor_id' => $otherMentor->id,
            'student_id' => $this->studentMember->id,
            'position' => 'EGKK_GND',
            'taken_date' => now()->subDay()->toDateString(),
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringSessionHistory::class)
            ->assertSee('EGKK_GND')
            ->assertSee($this->studentMember->cid);
    }

    #[Test]
    public function it_correctly_identifies_no_show_sessions(): void
    {
        $this->grantPositionAccess(['EGPH_TWR']);

        Session::factory()->create([
            'mentor_id' => $this->mentorMember->id,
            'noShow' => true,
            'position' => 'EGPH_TWR',
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringSessionHistory::class)
            ->assertSee('No-Show')
            ->assertSeeHtml('!bg-red-300');
    }

    #[Test]
    public function it_calculates_cancellation_colors_based_on_timing(): void
    {
        $this->grantPositionAccess(['EGPH_TWR']);

        Session::factory()->create([
            'mentor_id' => $this->mentorMember->id,
            'taken_date' => '2026-05-01',
            'taken_from' => '12:00:00',
            'cancelled_datetime' => '2026-05-01 11:30:00',
            'position' => 'EGPH_TWR',
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringSessionHistory::class)
            ->assertSee('Cancelled at Fri 1 May 2026 11:30')
            ->assertSeeHtml('!bg-red-200');
    }

    #[Test]
    public function it_renders_report_links_correctly(): void
    {
        $this->grantPositionAccess(['EGPH_TWR']);

        $session = Session::factory()->create([
            'mentor_id' => $this->mentorMember->id,
            'filed' => now(),
            'position' => 'EGPH_TWR',
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringSessionHistory::class)
            ->assertSee('View Report')
            ->assertSeeHtml("https://cts.vatsim.uk/mentors/report.php?id={$session->id}&view=report");
    }

    #[Test]
    public function it_filters_by_student_using_the_select_filter(): void
    {
        $this->grantPositionAccess(['EGPH_APP']);

        $otherStudent = Member::factory()->create(['joined_div' => now(), 'old_rts_id' => 0]);

        Session::factory()->create(['mentor_id' => $this->mentorMember->id, 'student_id' => $this->studentMember->id, 'position' => 'EGPH_APP']);
        Session::factory()->create(['mentor_id' => $this->mentorMember->id, 'student_id' => $otherStudent->id, 'position' => 'EGPH_APP']);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringSessionHistory::class)
            ->filterTable('student_id', $this->studentMember->id)
            ->assertSee($this->studentMember->cid)
            ->assertDontSee($otherStudent->cid);
    }

    #[Test]
    public function it_filters_by_position(): void
    {
        $this->grantPositionAccess(['EGPH_APP', 'EGLL_TWR']);

        Session::factory()->create(['mentor_id' => $this->mentorMember->id, 'position' => 'EGPH_APP']);
        Session::factory()->create(['mentor_id' => $this->mentorMember->id, 'position' => 'EGLL_TWR']);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringSessionHistory::class)
            ->filterTable('position', 'EGPH_APP')
            ->assertSee('EGPH_APP')
            ->assertDontSee('EGLL_TWR');
    }

    #[Test]
    public function it_filters_by_date_range(): void
    {
        $this->grantPositionAccess(['EGPH_APP']);

        Session::factory()->create(['mentor_id' => $this->mentorMember->id, 'taken_date' => '2026-01-01', 'position' => 'EGPH_APP']);
        Session::factory()->create(['mentor_id' => $this->mentorMember->id, 'taken_date' => '2026-04-20', 'position' => 'EGPH_APP']);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringSessionHistory::class)
            ->filterTable('taken_date', [
                'from' => '2026-04-01',
                'to' => '2026-04-30',
            ])
            ->assertSee('Mon 20 Apr 2026')
            ->assertDontSee('Thu 1 Jan 2026');
    }

    #[Test]
    public function it_filters_missing_reports(): void
    {
        $this->grantPositionAccess(['EGPH_APP']);

        Session::factory()->create(['mentor_id' => $this->mentorMember->id, 'filed' => now(), 'position' => 'EGPH_APP', 'student_id' => $this->studentMember->id]);

        $noReportStudent = Member::factory()->create(['joined_div' => now(), 'old_rts_id' => 0]);
        Session::factory()->create(['mentor_id' => $this->mentorMember->id, 'filed' => null, 'position' => 'EGPH_APP', 'student_id' => $noReportStudent->id]);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringSessionHistory::class)
            ->filterTable('needs_report', true)
            ->assertSee($noReportStudent->cid)
            ->assertDontSee($this->studentMember->cid);
    }

    #[Test]
    public function it_filters_to_personally_mentored_sessions_only(): void
    {
        $this->grantPositionAccess(['EGPH_APP']);

        Session::factory()->create(['mentor_id' => $this->mentorMember->id, 'position' => 'EGPH_APP', 'student_id' => $this->studentMember->id]);

        $otherMentor = Member::factory()->create(['joined_div' => now(), 'old_rts_id' => 0]);
        $otherStudent = Member::factory()->create(['joined_div' => now(), 'old_rts_id' => 0]);
        Session::factory()->create(['mentor_id' => $otherMentor->id, 'position' => 'EGPH_APP', 'student_id' => $otherStudent->id]);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringSessionHistory::class)
            ->assertSee($this->studentMember->cid)
            ->assertSee($otherStudent->cid)
            ->filterTable('only_my_mentoring', true)
            ->assertSee($this->studentMember->cid)
            ->assertDontSee($otherStudent->cid);
    }
}
