<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\MyTraining;

use App\Filament\Training\Pages\Mentor\ViewMentoringReport;
use App\Filament\Training\Pages\MyTraining\MyMentoringHistory;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class MyMentoringHistoryTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected Account $studentAccount;

    protected Member $studentMember;

    protected Member $mentorMember;

    protected Session $filedSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->studentAccount = Account::factory()->create();
        $this->studentMember = Member::factory()->create([
            'id' => $this->studentAccount->id,
            'cid' => $this->studentAccount->id,
        ]);

        $mentorAccount = Account::factory()->create();
        $this->mentorMember = Member::factory()->create([
            'id' => $mentorAccount->id,
            'cid' => $mentorAccount->id,
        ]);

        $this->filedSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGKK_TWR',
            'taken' => 1,
            'taken_date' => now()->subDays(3)->format('Y-m-d'),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
            'filed' => now()->subDays(2),
        ]);

        $this->studentAccount->givePermissionTo('training.access');
    }

    #[Test]
    public function member_with_training_access_can_view_my_mentoring_history_page(): void
    {
        Livewire::actingAs($this->studentAccount)
            ->test(MyMentoringHistory::class)
            ->assertSuccessful();
    }

    #[Test]
    public function member_without_training_access_cannot_view_my_mentoring_history_page(): void
    {
        $accountWithoutAccess = Account::factory()->create();

        Livewire::actingAs($accountWithoutAccess)
            ->test(MyMentoringHistory::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_shows_past_mentoring_sessions_for_the_authenticated_member_including_pending_reports(): void
    {
        $pendingSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'taken_date' => now()->subDay()->format('Y-m-d'),
            'taken_from' => '14:00:00',
            'taken_to' => '16:00:00',
            'filed' => null,
        ]);

        $otherAccount = Account::factory()->create();
        $otherMember = Member::factory()->create([
            'id' => $otherAccount->id,
            'cid' => $otherAccount->id,
        ]);

        Session::factory()->create([
            'student_id' => $otherMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGKK_APP',
            'taken' => 1,
            'taken_date' => now()->subDays(5)->format('Y-m-d'),
            'taken_from' => '18:00:00',
            'taken_to' => '20:00:00',
            'filed' => now()->subDays(4),
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyMentoringHistory::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$this->filedSession, $pendingSession]);
    }

    #[Test]
    public function it_shows_view_action_only_for_filed_sessions(): void
    {
        $pendingSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'taken_date' => now()->subDay()->format('Y-m-d'),
            'taken_from' => '14:00:00',
            'taken_to' => '16:00:00',
            'filed' => null,
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyMentoringHistory::class)
            ->assertTableActionVisible('view', $this->filedSession)
            ->assertTableActionHidden('view', $pendingSession);
    }

    #[Test]
    public function it_displays_the_correct_status_badge_for_pending_sessions(): void
    {
        $pendingSession = Session::factory()->create([
            'student_id' => $this->studentMember->id,
            'mentor_id' => $this->mentorMember->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'taken_date' => now()->subDay()->format('Y-m-d'),
            'taken_from' => '14:00:00',
            'taken_to' => '16:00:00',
            'filed' => null,
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyMentoringHistory::class)
            ->assertTableColumnStateSet('status', 'Pending', record: $pendingSession);
    }

    #[Test]
    public function it_shows_an_empty_table_when_member_has_no_past_mentoring_sessions(): void
    {
        $emptyAccount = Account::factory()->create();
        Member::factory()->create([
            'id' => $emptyAccount->id,
            'cid' => $emptyAccount->id,
        ]);
        $emptyAccount->givePermissionTo('training.access');

        Livewire::actingAs($emptyAccount)
            ->test(MyMentoringHistory::class)
            ->assertSuccessful()
            ->assertSee('No mentoring sessions found');
    }

    #[Test]
    public function member_can_view_their_own_mentoring_report_from_the_table(): void
    {
        Livewire::actingAs($this->studentAccount)
            ->test(ViewMentoringReport::class, ['sessionId' => $this->filedSession->id])
            ->assertSuccessful();
    }
}
