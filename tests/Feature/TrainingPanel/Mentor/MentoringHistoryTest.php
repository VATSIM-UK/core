<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\Mentor;

use App\Filament\Training\Pages\Mentor\MentoringHistory;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\MentorPermissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class MentoringHistoryTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_loads_when_user_has_at_least_one_mentoring_position(): void
    {
        $category = MentorPermissionService::atcCategories()[0];
        $trainingPosition = $this->createTrainingPosition($category, 'EGLL_GND');

        app(MentorPermissionService::class)->assignToMentorable($this->panelUser, $trainingPosition, $this->panelUser, $category);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringHistory::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_is_forbidden_when_user_has_no_mentoring_positions(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(MentoringHistory::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_defaults_to_the_first_visible_category_when_category_is_empty(): void
    {
        $category = MentorPermissionService::atcCategories()[0];
        $trainingPosition = $this->createTrainingPosition($category, 'EGLL_GND');

        app(MentorPermissionService::class)->assignToMentorable($this->panelUser, $trainingPosition, $this->panelUser, $category);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringHistory::class)
            ->assertSet('category', $category);
    }

    #[Test]
    public function it_defaults_to_the_first_visible_category_when_category_is_invalid(): void
    {
        $category = MentorPermissionService::atcCategories()[0];
        $trainingPosition = $this->createTrainingPosition($category, 'EGLL_GND');

        app(MentorPermissionService::class)->assignToMentorable($this->panelUser, $trainingPosition, $this->panelUser, $category);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringHistory::class, ['category' => 'Not A Real Category'])
            ->assertSet('category', $category);
    }

    #[Test]
    public function it_defaults_to_all_when_category_is_empty_and_multiple_groups_are_visible(): void
    {
        $categoryOne = MentorPermissionService::atcCategories()[0];
        $categoryTwo = MentorPermissionService::atcCategories()[1];

        $positionOne = $this->createTrainingPosition($categoryOne, 'EGLL_GND');
        $positionTwo = $this->createTrainingPosition($categoryTwo, 'EGKK_TWR');

        app(MentorPermissionService::class)->assignToMentorable($this->panelUser, $positionOne, $this->panelUser, $categoryOne);
        app(MentorPermissionService::class)->assignToMentorable($this->panelUser, $positionTwo, $this->panelUser, $categoryTwo);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringHistory::class)
            ->assertSet('category', MentorPermissionService::ALL_CATEGORIES);
    }

    #[Test]
    public function it_defaults_to_all_when_category_is_invalid_and_multiple_groups_are_visible(): void
    {
        $categoryOne = MentorPermissionService::atcCategories()[0];
        $categoryTwo = MentorPermissionService::atcCategories()[1];

        $positionOne = $this->createTrainingPosition($categoryOne, 'EGLL_GND');
        $positionTwo = $this->createTrainingPosition($categoryTwo, 'EGKK_TWR');

        app(MentorPermissionService::class)->assignToMentorable($this->panelUser, $positionOne, $this->panelUser, $categoryOne);
        app(MentorPermissionService::class)->assignToMentorable($this->panelUser, $positionTwo, $this->panelUser, $categoryTwo);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringHistory::class, ['category' => 'Not A Real Category'])
            ->assertSet('category', MentorPermissionService::ALL_CATEGORIES);
    }

    #[Test]
    public function it_shows_sessions_from_all_visible_categories_when_all_is_selected(): void
    {
        $categoryOne = MentorPermissionService::atcCategories()[0];
        $categoryTwo = MentorPermissionService::atcCategories()[1];

        $positionOne = $this->createTrainingPosition($categoryOne, 'EGKK_GND');
        $positionTwo = $this->createTrainingPosition($categoryTwo, 'EGKK_TWR');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);

        app(MentorPermissionService::class)->assignToMentorable($this->panelUser, $positionOne, $this->panelUser, $categoryOne);
        app(MentorPermissionService::class)->assignToMentorable($this->panelUser, $positionTwo, $this->panelUser, $categoryTwo);

        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $sessionInCategoryOne = $this->insertSession($mentorCtsMember->id, $studentCtsMember->id, 'EGKK_GND');
        $sessionInCategoryTwo = $this->insertSession($mentorCtsMember->id, $studentCtsMember->id, 'EGKK_TWR');

        $sessionOne = Session::on('cts')->find($sessionInCategoryOne);
        $sessionTwo = Session::on('cts')->find($sessionInCategoryTwo);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringHistory::class, ['category' => MentorPermissionService::ALL_CATEGORIES])
            ->assertSet('category', MentorPermissionService::ALL_CATEGORIES)
            ->assertCanSeeTableRecords([$sessionOne, $sessionTwo]);
    }

    #[Test]
    public function it_shows_sessions_for_the_selected_atc_category_only(): void
    {
        $categoryOne = MentorPermissionService::atcCategories()[0];
        $categoryTwo = MentorPermissionService::atcCategories()[1];

        $positionOne = $this->createTrainingPosition($categoryOne, 'EGKK_GND');
        $positionTwo = $this->createTrainingPosition($categoryTwo, 'EGKK_TWR');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);

        app(MentorPermissionService::class)->assignToMentorable($this->panelUser, $positionOne, $this->panelUser, $categoryOne);
        app(MentorPermissionService::class)->assignToMentorable($this->panelUser, $positionTwo, $this->panelUser, $categoryTwo);

        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $sessionInCategory = $this->insertSession($mentorCtsMember->id, $studentCtsMember->id, 'EGKK_GND');
        $sessionOutOfCategory = $this->insertSession($mentorCtsMember->id, $studentCtsMember->id, 'EGKK_TWR');

        $sessionInRecord = Session::on('cts')->find($sessionInCategory);
        $sessionOutRecord = Session::on('cts')->find($sessionOutOfCategory);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringHistory::class, ['category' => $categoryOne])
            ->assertCanSeeTableRecords([$sessionInRecord])
            ->assertCanNotSeeTableRecords([$sessionOutRecord]);
    }

    #[Test]
    public function it_shows_view_action_only_for_filed_sessions(): void
    {
        $category = MentorPermissionService::atcCategories()[0];
        $trainingPosition = $this->createTrainingPosition($category, 'EGNM_GND');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);
        app(MentorPermissionService::class)->assignToMentorable($this->panelUser, $trainingPosition, $this->panelUser, $category);

        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $filedId = $this->insertSession($mentorCtsMember->id, $studentCtsMember->id, 'EGNM_GND', filed: now()->toDateTimeString());
        $unfiledId = $this->insertSession($mentorCtsMember->id, $studentCtsMember->id, 'EGNM_GND', filed: null);

        $filedSession = Session::on('cts')->find($filedId);
        $unfiledSession = Session::on('cts')->find($unfiledId);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringHistory::class, ['category' => $category])
            ->assertTableActionVisible('view', $filedSession)
            ->assertTableActionHidden('view', $unfiledSession);
    }

    #[Test]
    public function it_displays_the_correct_status_badge_for_completed_sessions(): void
    {
        $category = MentorPermissionService::atcCategories()[0];
        $trainingPosition = $this->createTrainingPosition($category, 'EGNT_GND');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);
        app(MentorPermissionService::class)->assignToMentorable($this->panelUser, $trainingPosition, $this->panelUser, $category);

        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $sessionId = $this->insertSession($mentorCtsMember->id, $studentCtsMember->id, 'EGNT_GND', filed: now()->toDateTimeString());
        $session = Session::on('cts')->find($sessionId);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringHistory::class, ['category' => $category])
            ->assertTableColumnStateSet('status', 'Completed', record: $session);
    }

    #[Test]
    public function it_displays_the_correct_status_badge_for_no_show_sessions(): void
    {
        $category = MentorPermissionService::atcCategories()[0];
        $trainingPosition = $this->createTrainingPosition($category, 'EGNS_GND');

        $mentorCtsMember = $this->getOrCreateCtsMember($this->panelUser);
        app(MentorPermissionService::class)->assignToMentorable($this->panelUser, $trainingPosition, $this->panelUser, $category);

        $student = Account::factory()->create();
        $studentCtsMember = $this->getOrCreateCtsMember($student);

        $sessionId = $this->insertSession($mentorCtsMember->id, $studentCtsMember->id, 'EGNS_GND', noShow: 1, filed: null);
        $session = Session::on('cts')->find($sessionId);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringHistory::class, ['category' => $category])
            ->assertTableColumnStateSet('status', 'No Show', record: $session);
    }

    #[Test]
    public function it_displays_an_empty_state_when_no_sessions_exist_for_the_category(): void
    {
        $category = MentorPermissionService::atcCategories()[0];
        $trainingPosition = $this->createTrainingPosition($category, 'EGPO_GND');

        app(MentorPermissionService::class)->assignToMentorable($this->panelUser, $trainingPosition, $this->panelUser, $category);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringHistory::class, ['category' => $category])
            ->assertSee('No mentoring sessions found in this training group');
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
