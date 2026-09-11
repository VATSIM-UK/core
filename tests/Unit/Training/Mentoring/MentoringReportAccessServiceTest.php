<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Mentoring;

use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\MentoringReportAccessService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MentoringReportAccessServiceTest extends TestCase
{
    use DatabaseTransactions;

    private MentoringReportAccessService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(MentoringReportAccessService::class);
    }

    public static function mentorLadderProvider(): array
    {
        return [
            'S3 mentor sees S3' => ['S3 Training', 'S3 Training', true],
            'S3 mentor sees S2' => ['S3 Training', 'S2 Training', true],
            'S3 mentor sees OBS to S1' => ['S3 Training', 'OBS to S1 Training', true],
            'S3 mentor does not see C1' => ['S3 Training', 'C1 Training', false],
            'S2 mentor sees S2' => ['S2 Training', 'S2 Training', true],
            'S2 mentor does not see S3' => ['S2 Training', 'S3 Training', false],
            'Heathrow mentor sees Heathrow GMC' => ['Heathrow AIR', 'Heathrow GMC', true],
            'Heathrow mentor sees S2' => ['Heathrow GMC', 'S2 Training', true],
            'Heathrow mentor does not see S3' => ['Heathrow GMC', 'S3 Training', false],
        ];
    }

    #[Test]
    #[DataProvider('mentorLadderProvider')]
    public function mentor_access_follows_the_category_ladder(string $mentorCategory, string $sessionCategory, bool $expected): void
    {
        $mentor = $this->createMentor($mentorCategory);

        $session = $this->createFiledSession($sessionCategory);

        $this->assertSame($expected, $this->service->canViewReport($mentor, $session));
    }

    #[Test]
    public function mentor_cannot_view_a_filed_report_when_the_student_holds_no_training_place(): void
    {
        $mentor = $this->createMentor('S3 Training');

        $session = $this->createFiledSession('S3 Training');

        $this->assertFalse($this->service->canViewReport($mentor, $session));
    }

    #[Test]
    public function mentor_can_view_a_filed_report_when_the_student_holds_a_matching_training_place(): void
    {
        $mentor = $this->createMentor('S3 Training');

        $session = $this->createFiledSession('S3 Training');
        $this->createActiveTrainingPlace($session->studentAccount(), 'S3 Training');

        $this->assertTrue($this->service->canViewReport($mentor, $session));
    }

    #[Test]
    public function mentor_access_is_revoked_when_the_students_training_place_is_soft_deleted(): void
    {
        $mentor = $this->createMentor('S3 Training');

        $session = $this->createFiledSession('S3 Training');
        $place = $this->createActiveTrainingPlace($session->studentAccount(), 'S3 Training');
        $place->delete();

        $this->assertFalse($this->service->canViewReport($mentor, $session));
    }

    #[Test]
    public function mentor_access_for_a_lower_category_session_requires_a_covering_place(): void
    {
        $twrMentor = $this->createMentor('S2 Training');

        $s3StudentSession = $this->createFiledSession('S2 Training');
        $this->createActiveTrainingPlace($s3StudentSession->studentAccount(), 'S3 Training');

        $this->assertTrue($this->service->canViewReport($twrMentor, $s3StudentSession));
    }

    #[Test]
    public function heathrow_mentor_sees_heathrow_and_s2_history_for_a_heathrow_student(): void
    {
        $mentor = $this->createMentor('Heathrow GMC');

        $heathrowSession = $this->createFiledSession('Heathrow AIR');
        $this->createActiveTrainingPlace($heathrowSession->studentAccount(), 'Heathrow AIR');

        $this->assertTrue($this->service->canViewReport($mentor, $heathrowSession));

        $s2Session = $this->createFiledSession('S2 Training');
        $this->createActiveTrainingPlace($s2Session->studentAccount(), 'Heathrow APC');

        $this->assertTrue($this->service->canViewReport($mentor, $s2Session));
    }

    #[Test]
    public function conducting_mentor_access_is_also_revoked_when_the_student_holds_no_training_place(): void
    {
        $mentorAccount = Account::factory()->create();
        Member::factory()->forAccount($mentorAccount)->create();

        $session = Session::factory()->create([
            'mentor_id' => Member::where('cid', $mentorAccount->id)->first()->id,
            'position' => $this->callsignFor('S3 Training'),
            'filed' => now(),
        ]);

        $this->assertFalse($this->service->canViewReport($mentorAccount, $session));

        $this->createActiveTrainingPlace($session->studentAccount(), 'S3 Training');

        $this->assertTrue($this->service->canViewReport($mentorAccount, $session));
    }

    #[Test]
    public function view_all_users_see_any_filed_report(): void
    {
        $user = Account::factory()->create();
        $user->givePermissionTo(MentoringReportAccessService::VIEW_ALL_PERMISSION);

        $session = $this->createFiledSession('S3 Training');

        $this->assertTrue($this->service->canViewReport($user, $session));
    }

    #[Test]
    public function tgi_can_see_reports_for_their_training_group_and_below_without_a_training_place(): void
    {
        $tgi = Account::factory()->create();
        $tgi->assignRole('ATC APP Instructor');

        $s3Session = $this->createFiledSession('S3 Training');
        $s2Session = $this->createFiledSession('S2 Training');
        $c1Session = $this->createFiledSession('C1 Training');

        $this->assertTrue($this->service->canViewReport($tgi, $s3Session));
        $this->assertTrue($this->service->canViewReport($tgi, $s2Session));
        $this->assertFalse($this->service->canViewReport($tgi, $c1Session));
    }

    #[Test]
    public function tgi_who_also_mentors_c1_does_not_gain_c1_visibility_for_their_tg(): void
    {
        $tgi = Account::factory()->create();
        $tgi->assignRole('ATC NC Instructor');
        $this->createMentorForAccount($tgi, 'C1 Training');

        $c1Session = $this->createFiledSession('C1 Training');

        $this->assertFalse($this->service->canViewReport($tgi, $c1Session));
    }

    #[Test]
    public function tgi_ladder_access_applies_even_when_the_student_holds_no_training_place(): void
    {
        $tgi = Account::factory()->create();
        $tgi->assignRole('ATC Enroute Instructor');

        $session = $this->createFiledSession('S2 Training');

        $this->assertTrue($this->service->canViewReport($tgi, $session));
    }

    #[Test]
    public function student_can_view_their_own_filed_reports(): void
    {
        $session = $this->createFiledSession('S3 Training');

        $this->assertTrue($this->service->canViewReport($session->studentAccount(), $session));
    }

    #[Test]
    public function unfiled_reports_are_hidden_from_everyone(): void
    {
        $session = $this->createFiledSession('S3 Training');
        $session->update(['filed' => null]);
        $session->refresh();

        $student = $session->studentAccount();

        $this->assertFalse($this->service->canViewReport($student, $session));

        $viewAllUser = Account::factory()->create();
        $viewAllUser->givePermissionTo(MentoringReportAccessService::VIEW_ALL_PERMISSION);
        $this->assertFalse($this->service->canViewReport($viewAllUser, $session));
    }

    #[Test]
    public function sessions_at_unrecognised_positions_are_hidden(): void
    {
        $user = Account::factory()->create();
        $user->givePermissionTo(MentoringReportAccessService::VIEW_ALL_PERMISSION);

        $session = $this->createFiledSession('ZZZZ_UNKNOWN');

        $this->assertFalse($this->service->canViewReport($user, $session));
    }

    #[Test]
    public function tgi_categories_are_derived_from_their_roles(): void
    {
        $tgi = Account::factory()->create();
        $tgi->assignRole('ATC TWR Instructor');

        $this->assertSame(['S2 Training'], $this->service->tgiCategoriesFor($tgi));

        $heathrowTgi = Account::factory()->create();
        $heathrowTgi->assignRole('ATC Heathrow Instructor');

        $this->assertSame(
            ['Heathrow GMC', 'Heathrow AIR', 'Heathrow APC'],
            $this->service->tgiCategoriesFor($heathrowTgi)
        );
    }

    #[Test]
    public function active_training_place_categories_reflect_soft_deleted_places(): void
    {
        $account = Account::factory()->create();

        $active = $this->createActiveTrainingPlace($account, 'S3 Training');
        $deleted = $this->createActiveTrainingPlace($account, 'S2 Training');
        $deleted->delete();

        $this->assertSame(['S3 Training'], $this->service->activeTrainingPlaceCategoriesFor($account));

        $this->assertNull($active->fresh()->deleted_at);
    }

    #[Test]
    public function visible_categories_for_a_mentor_cover_their_ladder(): void
    {
        $mentor = $this->createMentor('S3 Training');

        $categories = $this->service->visibleCategoriesFor($mentor);

        $this->assertContains('S3 Training', $categories);
        $this->assertContains('S2 Training', $categories);
        $this->assertContains('OBS to S1 Training', $categories);
        $this->assertNotContains('C1 Training', $categories);
    }

    #[Test]
    public function visible_categories_for_a_view_all_user_cover_everything(): void
    {
        $user = Account::factory()->create();
        $user->givePermissionTo(MentoringReportAccessService::VIEW_ALL_PERMISSION);

        $categories = $this->service->visibleCategoriesFor($user);

        $this->assertContains('C1 Training', $categories);
        $this->assertContains('OBS to S1 Training', $categories);
        $this->assertContains('Heathrow GMC', $categories);
    }

    #[Test]
    public function visible_sessions_query_hides_filed_reports_for_students_without_a_qualifying_place(): void
    {
        $mentor = $this->createMentor('S3 Training');

        $placedStudent = Account::factory()->create();
        $placedStudentMember = Member::factory()->forAccount($placedStudent)->create();
        $this->createActiveTrainingPlace($placedStudent, 'S3 Training');

        $placelessStudent = Account::factory()->create();
        $placelessStudentMember = Member::factory()->forAccount($placelessStudent)->create();

        $visibleSession = $this->createFiledSessionFor('S3 Training', $placedStudentMember);
        $hiddenSession = $this->createFiledSessionFor('S3 Training', $placelessStudentMember);

        $ids = $this->service->visibleSessionsQueryFor($mentor)->pluck('id')->all();

        $this->assertContains($visibleSession->id, $ids);
        $this->assertNotContains($hiddenSession->id, $ids);
    }

    #[Test]
    public function an_obs_place_does_not_expose_the_students_historic_higher_rated_sessions(): void
    {
        $mentor = $this->createMentor('S3 Training');

        $student = Account::factory()->create();
        $studentMember = Member::factory()->forAccount($student)->create();
        $this->createActiveTrainingPlace($student, 'OBS to S1 Training');

        $historicS2 = $this->createFiledSessionFor('S2 Training', $studentMember);
        $placelessHistoricS3 = $this->createFiledSessionFor('S3 Training', $studentMember);

        $ids = $this->service->visibleSessionsQueryFor($mentor)->pluck('id')->all();

        $this->assertNotContains($historicS2->id, $ids);
        $this->assertNotContains($placelessHistoricS3->id, $ids);
    }

    #[Test]
    public function a_students_place_does_not_expose_another_students_sessions(): void
    {
        $mentor = $this->createMentor('S3 Training');

        $placedStudent = Account::factory()->create();
        $placedStudentMember = Member::factory()->forAccount($placedStudent)->create();
        $this->createActiveTrainingPlace($placedStudent, 'S3 Training');

        $otherPlacedStudent = Account::factory()->create();
        $otherPlacedStudentMember = Member::factory()->forAccount($otherPlacedStudent)->create();
        $this->createActiveTrainingPlace($otherPlacedStudent, 'S2 Training');

        $s3Session = $this->createFiledSessionFor('S3 Training', $placedStudentMember);
        $s2Session = $this->createFiledSessionFor('S2 Training', $otherPlacedStudentMember);

        $ids = $this->service->visibleSessionsQueryFor($mentor)->pluck('id')->all();

        $this->assertContains($s3Session->id, $ids);
        $this->assertContains($s2Session->id, $ids);

        // Now take away the S3 place: the S2 place must not keep the S3 session visible.
        TrainingPlace::query()
            ->where('account_id', $placedStudent->id)
            ->get()
            ->each(fn (TrainingPlace $place) => $place->delete());

        $ids = $this->service->visibleSessionsQueryFor($mentor)->pluck('id')->all();

        $this->assertNotContains($s3Session->id, $ids);
        $this->assertContains($s2Session->id, $ids);
    }

    #[Test]
    public function visible_sessions_query_applies_place_gating_to_pending_sessions(): void
    {
        $mentor = $this->createMentor('S3 Training');

        $placedStudent = Account::factory()->create();
        $placedStudentMember = Member::factory()->forAccount($placedStudent)->create();
        $this->createActiveTrainingPlace($placedStudent, 'S3 Training');

        $placelessStudent = Account::factory()->create();
        $placelessStudentMember = Member::factory()->forAccount($placelessStudent)->create();

        $pendingVisible = Session::factory()->create([
            'student_id' => $placedStudentMember->id,
            'position' => $this->callsignFor('S3 Training'),
            'filed' => null,
        ]);

        $pendingHidden = Session::factory()->create([
            'student_id' => $placelessStudentMember->id,
            'position' => $this->callsignFor('S3 Training'),
            'filed' => null,
        ]);

        $ids = $this->service->visibleSessionsQueryFor($mentor)->pluck('id')->all();

        $this->assertContains($pendingVisible->id, $ids);
        $this->assertNotContains($pendingHidden->id, $ids);
    }

    #[Test]
    public function visible_sessions_query_applies_the_category_ladder_to_pending_sessions(): void
    {
        $mentor = $this->createMentor('S2 Training');

        $student = Account::factory()->create();
        $studentMember = Member::factory()->forAccount($student)->create();
        $this->createActiveTrainingPlace($student, 'OBS to S1 Training');

        $pendingS2 = Session::factory()->create([
            'student_id' => $studentMember->id,
            'position' => $this->callsignFor('S2 Training'),
            'filed' => null,
        ]);

        $pendingS1 = Session::factory()->create([
            'student_id' => $studentMember->id,
            'position' => $this->callsignFor('OBS to S1 Training'),
            'filed' => null,
        ]);

        $ids = $this->service->visibleSessionsQueryFor($mentor)->pluck('id')->all();

        $this->assertNotContains($pendingS2->id, $ids);
        $this->assertContains($pendingS1->id, $ids);
    }

    #[Test]
    public function visible_sessions_query_scopes_tgi_rows_to_their_ladder(): void
    {
        $tgi = Account::factory()->create();
        $tgi->assignRole('ATC APP Instructor');

        $student = Account::factory()->create();
        $studentMember = Member::factory()->forAccount($student)->create();

        $s3Session = $this->createFiledSessionFor('S3 Training', $studentMember);
        $s2Session = $this->createFiledSessionFor('S2 Training', $studentMember);
        $c1Session = $this->createFiledSessionFor('C1 Training', $studentMember);

        $ids = $this->service->visibleSessionsQueryFor($tgi)->pluck('id')->all();

        $this->assertContains($s3Session->id, $ids);
        $this->assertContains($s2Session->id, $ids);
        $this->assertNotContains($c1Session->id, $ids);
    }

    #[Test]
    public function visible_sessions_query_is_empty_for_a_user_with_no_access(): void
    {
        $account = Account::factory()->create();
        Member::factory()->forAccount($account)->create();

        $query = $this->service->visibleSessionsQueryFor($account);

        $this->assertSame(0, $query->count());
    }

    #[Test]
    public function visible_sessions_query_returns_everything_for_view_all_users(): void
    {
        $user = Account::factory()->create();
        $user->givePermissionTo(MentoringReportAccessService::VIEW_ALL_PERMISSION);

        $total = Session::query()->count();

        $this->assertSame($total, $this->service->visibleSessionsQueryFor($user)->count());
    }

    #[Test]
    public function visible_sessions_query_shows_conducted_sessions_for_place_holding_students(): void
    {
        $mentorAccount = Account::factory()->create();
        $mentorMember = Member::factory()->forAccount($mentorAccount)->create();

        $student = Account::factory()->create();
        $studentMember = Member::factory()->forAccount($student)->create();
        $this->createActiveTrainingPlace($student, 'S3 Training');

        $conductedVisible = Session::factory()->create([
            'mentor_id' => $mentorMember->id,
            'student_id' => $studentMember->id,
            'position' => $this->callsignFor('S3 Training'),
            'filed' => now(),
        ]);

        $otherStudent = Account::factory()->create();
        $otherStudentMember = Member::factory()->forAccount($otherStudent)->create();

        $conductedHidden = Session::factory()->create([
            'mentor_id' => $mentorMember->id,
            'student_id' => $otherStudentMember->id,
            'position' => $this->callsignFor('S3 Training'),
            'filed' => now(),
        ]);

        $ids = $this->service->visibleSessionsQueryFor($mentorAccount)->pluck('id')->all();

        $this->assertContains($conductedVisible->id, $ids);
        $this->assertNotContains($conductedHidden->id, $ids);
    }

    #[Test]
    public function visible_sessions_query_revokes_conducted_sessions_once_the_place_is_gone(): void
    {
        $mentorAccount = Account::factory()->create();
        $mentorMember = Member::factory()->forAccount($mentorAccount)->create();

        $student = Account::factory()->create();
        $studentMember = Member::factory()->forAccount($student)->create();

        $conducted = Session::factory()->create([
            'mentor_id' => $mentorMember->id,
            'student_id' => $studentMember->id,
            'position' => $this->callsignFor('S3 Training'),
            'filed' => now(),
        ]);

        $this->assertNotContains($conducted->id, $this->service->visibleSessionsQueryFor($mentorAccount)->pluck('id')->all());

        $this->createActiveTrainingPlace($student, 'S3 Training');

        $this->assertContains($conducted->id, $this->service->visibleSessionsQueryFor($mentorAccount)->pluck('id')->all());
    }

    private function callsignFor(string $category): string
    {
        return match ($category) {
            'C1 Training' => 'EGTT_CTR',
            'S3 Training' => 'EGLL_APP',
            'S2 Training' => 'EGLL_TWR',
            'OBS to S1 Training' => 'EGLL_GND',
            'Heathrow GMC' => 'EGLL_TWR',
            'Heathrow AIR' => 'EGLL_APP',
            'Heathrow APC' => 'EGKK_APP',
            default => 'EGLL_APP',
        };
    }

    private function createMentor(string $category): Account
    {
        return $this->createMentorForAccount(Account::factory()->create(), $category);
    }

    private function createMentorForAccount(Account $account, string $category): Account
    {
        $trainingPosition = TrainingPosition::factory()->create([
            'category' => $category,
            'cts_positions' => [$this->callsignFor($category)],
        ]);

        MentorTrainingPosition::create([
            'account_id' => $account->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $trainingPosition->id,
            'created_by' => $account->id,
        ]);

        return $account;
    }

    private function createFiledSession(string $category): Session
    {
        $account = Account::factory()->create();
        $member = Member::factory()->forAccount($account)->create();

        return $this->createFiledSessionFor($category, $member);
    }

    private function createFiledSessionFor(string $category, Member $studentMember): Session
    {
        return Session::factory()->create([
            'student_id' => $studentMember->id,
            'position' => $this->callsignFor($category),
            'filed' => now(),
        ]);
    }

    private function createActiveTrainingPlace(Account $account, string $category): TrainingPlace
    {
        $position = TrainingPosition::factory()->create([
            'category' => $category,
            'cts_positions' => [$this->callsignFor($category)],
        ]);

        $place = TrainingPlace::factory()
            ->forTrainingPosition($position)
            ->createQuietly(['account_id' => $account->id]);

        return $place->refresh();
    }
}
