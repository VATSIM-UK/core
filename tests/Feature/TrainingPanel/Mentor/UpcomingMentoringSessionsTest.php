<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\Mentor;

use App\Filament\Training\Pages\Mentor\UpcomingMentoringSessions;
use App\Livewire\Training\PendingMentoringReportsTable;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\MentorPermissionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\CalendarLinks\Link;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class UpcomingMentoringSessionsTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_loads_when_user_has_atc_view_permission(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGLL_GND');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_is_forbidden_when_user_has_no_view_permission(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_defaults_to_all_when_category_is_empty_and_multiple_groups_are_visible(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGLL_GND');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class)
            ->assertSet('category', MentorPermissionService::ALL_CATEGORIES);
    }

    #[Test]
    public function it_defaults_to_all_when_category_is_invalid_and_multiple_groups_are_visible(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGLL_GND');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class, ['category' => 'Not A Real Category'])
            ->assertSet('category', MentorPermissionService::ALL_CATEGORIES);
    }

    #[Test]
    public function it_shows_future_accepted_sessions_for_the_selected_category(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGPH_GND');

        $mentor = Account::factory()->create();
        $mentorCtsMember = $this->getOrCreateCtsMember($mentor);

        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $sessionId = $this->insertSession(
            $mentorCtsMember->id,
            $studentCtsMember->id,
            'EGPH_GND',
            takenDate: Carbon::tomorrow()->format('Y-m-d H:i:s'),
        );
        $session = Session::on('cts')->find($sessionId);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class, ['category' => $category])
            ->assertCanSeeTableRecords([$session]);
    }

    #[Test]
    public function it_does_not_show_past_sessions(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGPK_GND');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);
        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $sessionId = $this->insertSession(
            $mentorCtsMember->id,
            $studentCtsMember->id,
            'EGPK_GND',
            takenDate: Carbon::yesterday()->format('Y-m-d H:i:s'),
        );
        $session = Session::on('cts')->find($sessionId);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class, ['category' => $category])
            ->assertCanNotSeeTableRecords([$session]);
    }

    #[Test]
    public function it_does_not_show_filed_sessions(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGPL_GND');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);
        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $sessionId = $this->insertSession(
            $mentorCtsMember->id,
            $studentCtsMember->id,
            'EGPL_GND',
            filed: now()->toDateTimeString(),
            takenDate: Carbon::tomorrow()->format('Y-m-d H:i:s'),
        );
        $session = Session::on('cts')->find($sessionId);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class, ['category' => $category])
            ->assertCanNotSeeTableRecords([$session]);
    }

    #[Test]
    public function it_does_not_show_cancelled_sessions(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGPQ_GND');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);
        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $sessionId = $this->insertSession(
            $mentorCtsMember->id,
            $studentCtsMember->id,
            'EGPQ_GND',
            cancelledDatetime: now()->toDateTimeString(),
            takenDate: Carbon::tomorrow()->format('Y-m-d H:i:s'),
        );
        $session = Session::on('cts')->find($sessionId);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class, ['category' => $category])
            ->assertCanNotSeeTableRecords([$session]);
    }

    #[Test]
    public function it_does_not_show_no_show_sessions(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGPR_GND');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);
        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $sessionId = $this->insertSession(
            $mentorCtsMember->id,
            $studentCtsMember->id,
            'EGPR_GND',
            noShow: 1,
            takenDate: Carbon::tomorrow()->format('Y-m-d H:i:s'),
        );
        $session = Session::on('cts')->find($sessionId);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class, ['category' => $category])
            ->assertCanNotSeeTableRecords([$session]);
    }

    #[Test]
    public function it_shows_sessions_from_all_visible_categories_when_all_is_selected(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $categoryOne = MentorPermissionService::atcCategories()[0];
        $categoryTwo = MentorPermissionService::atcCategories()[1];

        $this->createTrainingPosition($categoryOne, 'EGKK_GND');
        $this->createTrainingPosition($categoryTwo, 'EGKK_TWR');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);
        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $tomorrow = Carbon::tomorrow()->format('Y-m-d H:i:s');

        $sessionInCategoryOne = $this->insertSession($mentorCtsMember->id, $studentCtsMember->id, 'EGKK_GND', takenDate: $tomorrow);
        $sessionInCategoryTwo = $this->insertSession($mentorCtsMember->id, $studentCtsMember->id, 'EGKK_TWR', takenDate: $tomorrow);

        $sessionOne = Session::on('cts')->find($sessionInCategoryOne);
        $sessionTwo = Session::on('cts')->find($sessionInCategoryTwo);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class, ['category' => MentorPermissionService::ALL_CATEGORIES])
            ->assertSet('category', MentorPermissionService::ALL_CATEGORIES)
            ->assertCanSeeTableRecords([$sessionOne, $sessionTwo]);
    }

    #[Test]
    public function it_shows_sessions_for_the_selected_category_only(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $categoryOne = MentorPermissionService::atcCategories()[0];
        $categoryTwo = MentorPermissionService::atcCategories()[1];

        $this->createTrainingPosition($categoryOne, 'EGKK_GND');
        $this->createTrainingPosition($categoryTwo, 'EGKK_TWR');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);
        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $tomorrow = Carbon::tomorrow()->format('Y-m-d H:i:s');

        $sessionInCategory = $this->insertSession($mentorCtsMember->id, $studentCtsMember->id, 'EGKK_GND', takenDate: $tomorrow);
        $sessionOutOfCategory = $this->insertSession($mentorCtsMember->id, $studentCtsMember->id, 'EGKK_TWR', takenDate: $tomorrow);

        $sessionInRecord = Session::on('cts')->find($sessionInCategory);
        $sessionOutRecord = Session::on('cts')->find($sessionOutOfCategory);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class, ['category' => $categoryOne])
            ->assertCanSeeTableRecords([$sessionInRecord])
            ->assertCanNotSeeTableRecords([$sessionOutRecord]);
    }

    #[Test]
    public function it_shows_sessions_for_all_positions_in_the_training_group(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGKK_GND');
        $this->createTrainingPosition($category, 'EGKK_TWR');

        $otherMentor = Account::factory()->create();
        $otherMentorCtsMember = $this->getOrCreateCtsMember($otherMentor);

        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $sessionId = $this->insertSession(
            $otherMentorCtsMember->id,
            $studentCtsMember->id,
            'EGKK_TWR',
            takenDate: Carbon::tomorrow()->format('Y-m-d H:i:s'),
        );
        $session = Session::on('cts')->find($sessionId);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class, ['category' => $category])
            ->assertCanSeeTableRecords([$session]);
    }

    #[Test]
    public function it_has_no_table_record_actions(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGPF_GND');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);
        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $sessionId = $this->insertSession(
            $mentorCtsMember->id,
            $studentCtsMember->id,
            'EGPF_GND',
            takenDate: Carbon::tomorrow()->format('Y-m-d H:i:s'),
        );
        $session = Session::on('cts')->find($sessionId);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class, ['category' => $category])
            ->assertCanSeeTableRecords([$session])
            ->assertDontSee('View Report');
    }

    #[Test]
    public function it_displays_an_empty_state_when_no_upcoming_sessions_exist(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGPO_GND');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class, ['category' => $category])
            ->assertSee('No upcoming mentoring sessions in this training group');
    }

    #[Test]
    public function pending_reports_table_shows_past_sessions_without_filed_reports(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGPD_GND');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);
        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $sessionId = $this->insertSession(
            $mentorCtsMember->id,
            $studentCtsMember->id,
            'EGPD_GND',
            takenDate: Carbon::yesterday()->format('Y-m-d H:i:s'),
        );
        $session = Session::on('cts')->find($sessionId);

        Livewire::actingAs($this->panelUser)
            ->test(PendingMentoringReportsTable::class, ['category' => $category])
            ->assertCanSeeTableRecords([$session]);
    }

    #[Test]
    public function pending_reports_table_does_not_show_future_sessions(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGPE_GND');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);
        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $sessionId = $this->insertSession(
            $mentorCtsMember->id,
            $studentCtsMember->id,
            'EGPE_GND',
            takenDate: Carbon::tomorrow()->format('Y-m-d H:i:s'),
        );
        $session = Session::on('cts')->find($sessionId);

        Livewire::actingAs($this->panelUser)
            ->test(PendingMentoringReportsTable::class, ['category' => $category])
            ->assertCanNotSeeTableRecords([$session]);
    }

    #[Test]
    public function it_builds_calendar_link_object_with_correct_properties(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGLL_APP');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);
        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $sessionId = $this->insertSession(
            $mentorCtsMember->id,
            $studentCtsMember->id,
            'EGLL_APP',
            takenDate: Carbon::tomorrow()->format('Y-m-d H:i:s'),
        );
        DB::connection('cts')->table('sessions')->where('id', $sessionId)->update(['taken_to' => '12:00:00']);
        $session = Session::on('cts')->find($sessionId);

        $method = new \ReflectionMethod(UpcomingMentoringSessions::class, 'buildCalendarLinkObject');
        $page = new UpcomingMentoringSessions;
        $link = $method->invoke($page, $session);

        $this->assertInstanceOf(Link::class, $link);
        $this->assertSame('Mentoring Session - EGLL_APP', $link->title);
        $this->assertSame($session->taken_date.' 10:00:00', $link->from->format('Y-m-d H:i:s'));
        $this->assertSame($session->taken_date.' 12:00:00', $link->to->format('Y-m-d H:i:s'));
        $this->assertStringContainsString('Position: EGLL_APP', $link->description);
        $this->assertSame('EGLL_APP', $link->address);
    }

    #[Test]
    public function pending_reports_table_does_not_show_filed_sessions(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGPJ_GND');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);
        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $sessionId = $this->insertSession(
            $mentorCtsMember->id,
            $studentCtsMember->id,
            'EGPJ_GND',
            filed: now()->toDateTimeString(),
            takenDate: Carbon::yesterday()->format('Y-m-d H:i:s'),
        );
        $session = Session::on('cts')->find($sessionId);

        Livewire::actingAs($this->panelUser)
            ->test(PendingMentoringReportsTable::class, ['category' => $category])
            ->assertCanNotSeeTableRecords([$session]);
    }

    #[Test]
    public function pending_reports_table_does_not_show_cancelled_or_no_show_sessions(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGPM_GND');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);
        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $yesterday = Carbon::yesterday()->format('Y-m-d H:i:s');

        $cancelledId = $this->insertSession(
            $mentorCtsMember->id,
            $studentCtsMember->id,
            'EGPM_GND',
            cancelledDatetime: now()->toDateTimeString(),
            takenDate: $yesterday,
        );
        $noShowId = $this->insertSession(
            $mentorCtsMember->id,
            $studentCtsMember->id,
            'EGPM_GND',
            noShow: 1,
            takenDate: $yesterday,
        );

        $cancelledSession = Session::on('cts')->find($cancelledId);
        $noShowSession = Session::on('cts')->find($noShowId);

        Livewire::actingAs($this->panelUser)
            ->test(PendingMentoringReportsTable::class, ['category' => $category])
            ->assertCanNotSeeTableRecords([$cancelledSession, $noShowSession]);
    }

    #[Test]
    public function pending_reports_table_scopes_to_the_selected_category(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $categoryOne = MentorPermissionService::atcCategories()[0];
        $categoryTwo = MentorPermissionService::atcCategories()[1];

        $this->createTrainingPosition($categoryOne, 'EGPC_GND');
        $this->createTrainingPosition($categoryTwo, 'EGPC_TWR');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);
        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $yesterday = Carbon::yesterday()->format('Y-m-d H:i:s');

        $sessionInCategory = $this->insertSession($mentorCtsMember->id, $studentCtsMember->id, 'EGPC_GND', takenDate: $yesterday);
        $sessionOutOfCategory = $this->insertSession($mentorCtsMember->id, $studentCtsMember->id, 'EGPC_TWR', takenDate: $yesterday);

        $sessionInRecord = Session::on('cts')->find($sessionInCategory);
        $sessionOutRecord = Session::on('cts')->find($sessionOutOfCategory);

        Livewire::actingAs($this->panelUser)
            ->test(PendingMentoringReportsTable::class, ['category' => $categoryOne])
            ->assertCanSeeTableRecords([$sessionInRecord])
            ->assertCanNotSeeTableRecords([$sessionOutRecord]);
    }

    #[Test]
    public function reallocate_button_is_visible_for_users_with_reallocate_permission(): void
    {
        $this->panelUser->givePermissionTo('training.mentoring.sessions.reallocate.*');
        $this->panelUser->givePermissionTo('training.mentoring.view.*');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGPH_GND');

        $mentor = Account::factory()->create();
        $mentorCtsMember = $this->getOrCreateCtsMember($mentor);

        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $sessionId = $this->insertSession(
            $mentorCtsMember->id,
            $studentCtsMember->id,
            'EGPH_GND',
            takenDate: Carbon::tomorrow()->format('Y-m-d H:i:s'),
        );
        $session = Session::on('cts')->find($sessionId);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class, ['category' => $category])
            ->assertCanSeeTableRecords([$session])
            ->assertSee('Reallocate');
    }

    #[Test]
    public function reallocate_button_is_hidden_for_users_without_manage_permission(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGPH_GND');

        $mentor = Account::factory()->create();
        $mentorCtsMember = $this->getOrCreateCtsMember($mentor);

        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $sessionId = $this->insertSession(
            $mentorCtsMember->id,
            $studentCtsMember->id,
            'EGPH_GND',
            takenDate: Carbon::tomorrow()->format('Y-m-d H:i:s'),
        );
        $session = Session::on('cts')->find($sessionId);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class, ['category' => $category])
            ->assertCanSeeTableRecords([$session])
            ->assertDontSee('Reallocate');
    }

    #[Test]
    public function the_page_renders_the_pending_reports_section(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        $category = MentorPermissionService::atcCategories()[0];
        $this->createTrainingPosition($category, 'EGPN_GND');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class, ['category' => $category])
            ->assertSee('Pending Reports')
            ->assertSee('Upcoming Sessions');
    }

    private function createTrainingPosition(string $category, string $callsign): TrainingPosition
    {
        CtsPosition::firstOrCreate(['callsign' => $callsign]);

        return TrainingPosition::factory()->create([
            'category' => $category,
            'cts_positions' => [$callsign],
        ]);
    }

    private function insertSession(
        int $mentorId,
        int $studentId,
        string $position,
        int $taken = 1,
        int $noShow = 0,
        ?string $filed = null,
        ?string $cancelledDatetime = null,
        ?string $takenDate = null,
    ): int {
        return DB::connection('cts')->table('sessions')->insertGetId([
            'mentor_id' => $mentorId,
            'student_id' => $studentId,
            'position' => $position,
            'progress_sheet_id' => 1,
            'taken' => $taken,
            'session_done' => $filed !== null ? 1 : 0,
            'noShow' => $noShow,
            'filed' => $filed,
            'cancelled_datetime' => $cancelledDatetime,
            'taken_date' => $takenDate ?? now()->format('Y-m-d H:i:s'),
            'taken_from' => '10:00:00',
        ]);
    }

    private function getOrCreateCtsMember(Account $account): Member
    {
        return Member::on('cts')->firstOrCreate(
            ['cid' => $account->id],
            [
                'id' => $account->id,
                'old_rts_id' => 0,
                'name' => $account->name,
                'joined' => now(),
                'joined_div' => now(),
            ]
        );
    }
}
