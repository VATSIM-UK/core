<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Mentoring;

use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\Mentoring\MentoringScope;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Policies\Training\Mentoring\MentoringPolicy;
use App\Services\Training\MentoringReportAccessService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MentoringPolicyTest extends TestCase
{
    use DatabaseTransactions;

    private MentoringPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = app(MentoringPolicy::class);
    }

    #[Test]
    public function view_any_allows_users_with_mentor_training_positions(): void
    {
        $account = $this->createMentorWithPosition('EGLL_APP');

        $this->assertTrue($this->policy->viewAny($account));
    }

    #[Test]
    public function view_any_allows_users_with_view_all_permission(): void
    {
        $account = Account::factory()->create();
        $account->givePermissionTo('training.mentoring.view.*');

        $this->assertTrue($this->policy->viewAny($account));
    }

    #[Test]
    public function view_any_allows_users_with_the_reports_view_all_permission(): void
    {
        $account = Account::factory()->create();
        $account->givePermissionTo(MentoringReportAccessService::VIEW_ALL_PERMISSION);

        $this->assertTrue($this->policy->viewAny($account));
    }

    #[Test]
    public function view_any_allows_role_only_tgis(): void
    {
        Role::firstOrCreate(['name' => 'ATC APP Instructor', 'guard_name' => 'web']);

        $tgi = Account::factory()->create();
        $tgi->assignRole('ATC APP Instructor');

        $this->assertTrue($this->policy->viewAny($tgi));
    }

    #[Test]
    public function view_any_denies_users_without_mentoring_access(): void
    {
        $account = Account::factory()->create();

        $this->assertFalse($this->policy->viewAny($account));
    }

    #[Test]
    public function view_allows_the_student_who_attended_the_session(): void
    {
        $student = Account::factory()->create();
        $session = $this->createSessionForStudent($student, filed: now());

        $this->assertTrue($this->policy->view($student, $session));
    }

    #[Test]
    public function view_allows_the_student_when_member_id_differs_from_account_id(): void
    {
        $student = Account::factory()->create();
        $studentMember = Member::factory()->create([
            'id' => $student->id + 1000,
            'cid' => $student->id,
        ]);
        $session = Session::factory()->create([
            'student_id' => $studentMember->id,
            'filed' => now(),
        ]);

        $this->assertTrue($this->policy->view($student, $session));
    }

    #[Test]
    public function view_allows_the_mentor_who_conducted_the_session_when_the_student_holds_a_place(): void
    {
        $student = Account::factory()->create();
        $studentMember = Member::factory()->forAccount($student)->create();

        $mentor = Account::factory()->create();
        $mentorMember = Member::factory()->forAccount($mentor)->create();
        $session = Session::factory()->create([
            'student_id' => $studentMember->id,
            'mentor_id' => $mentorMember->id,
            'filed' => now(),
        ]);

        $this->createTrainingPlaceFor($student, 'S3 Training');

        $this->assertTrue($this->policy->view($mentor, $session));
    }

    #[Test]
    public function view_denies_the_conducting_mentor_when_the_student_holds_no_training_place(): void
    {
        $mentor = Account::factory()->create();
        $mentorMember = Member::factory()->forAccount($mentor)->create();
        $session = Session::factory()->create([
            'mentor_id' => $mentorMember->id,
            'filed' => now(),
        ]);

        $this->assertFalse($this->policy->view($mentor, $session));
    }

    #[Test]
    public function view_allows_a_mentor_with_permission_for_the_session_position_when_the_student_holds_a_place(): void
    {
        $student = Account::factory()->create();
        $studentMember = Member::factory()->forAccount($student)->create();

        $mentor = $this->createMentorWithPosition('EGLL_APP');
        $session = Session::factory()->create([
            'student_id' => $studentMember->id,
            'position' => 'EGLL_APP',
            'filed' => now(),
        ]);

        $this->createTrainingPlaceFor($student, 'S3 Training');

        $this->assertTrue($this->policy->view($mentor, $session));
    }

    #[Test]
    public function view_denies_a_mentor_when_the_student_holds_no_training_place(): void
    {
        $mentor = $this->createMentorWithPosition('EGLL_APP');
        $session = Session::factory()->create([
            'position' => 'EGLL_APP',
            'filed' => now(),
        ]);

        $this->assertFalse($this->policy->view($mentor, $session));
    }

    #[Test]
    public function view_allows_a_tgi_for_their_training_group_and_below(): void
    {
        Role::firstOrCreate(['name' => 'ATC APP Instructor', 'guard_name' => 'web']);

        // The callsign-to-category mapping is derived from TrainingPosition rows.
        TrainingPosition::factory()->create([
            'category' => 'S2 Training',
            'cts_positions' => ['EGLL_TWR'],
        ]);

        $tgi = Account::factory()->create();
        $tgi->assignRole('ATC APP Instructor');

        $s2Session = Session::factory()->create([
            'position' => 'EGLL_TWR',
            'filed' => now(),
        ]);

        $this->assertTrue($this->policy->view($tgi, $s2Session));
    }

    #[Test]
    public function view_allows_holders_of_the_reports_view_all_permission(): void
    {
        $staff = Account::factory()->create();
        $staff->givePermissionTo(MentoringReportAccessService::VIEW_ALL_PERMISSION);

        $session = Session::factory()->create([
            'position' => 'EGLL_APP',
            'filed' => now(),
        ]);

        $this->assertTrue($this->policy->view($staff, $session));
    }

    #[Test]
    public function gate_allows_a_mentor_with_permission_for_the_session_position_when_the_student_holds_a_place(): void
    {
        $student = Account::factory()->create();
        $studentMember = Member::factory()->forAccount($student)->create();

        $mentor = $this->createMentorWithPosition('EGLL_APP');
        $session = Session::factory()->create([
            'student_id' => $studentMember->id,
            'position' => 'EGLL_APP',
            'filed' => now(),
        ]);

        $this->createTrainingPlaceFor($student, 'S3 Training');

        $this->assertTrue(Gate::forUser($mentor)->allows('view', $session));
    }

    #[Test]
    public function view_denies_unfiled_reports_even_for_the_student(): void
    {
        $student = Account::factory()->create();
        $session = $this->createSessionForStudent($student);

        $this->assertFalse($this->policy->view($student, $session));
    }

    #[Test]
    public function view_denies_unfiled_reports_even_for_the_mentor(): void
    {
        $mentor = Account::factory()->create();
        $mentorMember = Member::factory()->forAccount($mentor)->create();
        $session = Session::factory()->create(['mentor_id' => $mentorMember->id]);

        $this->assertFalse($this->policy->view($mentor, $session));
    }

    #[Test]
    public function view_denies_unfiled_reports_even_for_view_all_users(): void
    {
        $admin = Account::factory()->create();
        $admin->givePermissionTo('training.mentoring.view.*');
        $session = Session::factory()->create(['position' => 'EGLL_APP']);

        $this->assertFalse($this->policy->view($admin, $session));
    }

    #[Test]
    public function view_denies_unrelated_users(): void
    {
        $account = Account::factory()->create();
        $session = Session::factory()->create([
            'position' => 'EGLL_APP',
            'filed' => now(),
        ]);

        $this->assertFalse($this->policy->view($account, $session));
    }

    #[Test]
    public function view_category_allows_assigned_mentors(): void
    {
        $mentor = $this->createMentorWithPosition('EGLL_APP', 'S3 Training');

        $this->assertTrue($this->policy->viewCategory($mentor, new MentoringScope, 'S3 Training'));
    }

    #[Test]
    public function view_category_denies_unassigned_categories(): void
    {
        $mentor = $this->createMentorWithPosition('EGLL_APP', 'S3 Training');

        $this->assertFalse($this->policy->viewCategory($mentor, new MentoringScope, 'S2 Training'));
    }

    #[Test]
    public function reallocate_allows_users_with_view_all_permission(): void
    {
        $admin = Account::factory()->create();
        $admin->givePermissionTo('training.mentoring.view.*');

        $session = Session::factory()->create(['position' => 'EGLL_APP']);

        $this->assertTrue($this->policy->reallocate($admin, $session));
    }

    #[Test]
    public function reallocate_allows_users_with_reallocate_permission(): void
    {
        $manager = Account::factory()->create();
        $manager->givePermissionTo('training.mentoring.sessions.reallocate.*');

        $session = Session::factory()->create(['position' => 'EGLL_APP']);

        $this->assertTrue($this->policy->reallocate($manager, $session));
    }

    #[Test]
    public function reallocate_denies_users_without_reallocate_permission(): void
    {
        $account = Account::factory()->create();
        $session = Session::factory()->create(['position' => 'EGLL_APP']);

        $this->assertFalse($this->policy->reallocate($account, $session));
    }

    #[Test]
    public function visible_cts_positions_for_category_includes_all_positions_for_view_all_users(): void
    {
        $admin = Account::factory()->create();
        $admin->givePermissionTo('training.mentoring.view.*');

        TrainingPosition::factory()->create([
            'category' => 'S3 Training',
            'cts_positions' => ['EGLL_APP', 'EGKK_APP'],
        ]);

        $positions = $this->policy->visibleCtsPositionsForCategory($admin, new MentoringScope, 'S3 Training');

        $this->assertContains('EGLL_APP', $positions);
        $this->assertContains('EGKK_APP', $positions);
    }

    private function createMentorWithPosition(string $callsign, string $category = 'S3 Training'): Account
    {
        $account = Account::factory()->create();

        $trainingPosition = TrainingPosition::factory()->create([
            'category' => $category,
            'cts_positions' => [$callsign],
        ]);

        MentorTrainingPosition::create([
            'account_id' => $account->id,
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => $trainingPosition->id,
            'created_by' => $account->id,
        ]);

        return $account;
    }

    private function createTrainingPlaceFor(Account $student, string $category): TrainingPlace
    {
        return TrainingPlace::factory()
            ->forTrainingPosition(TrainingPosition::factory()->create([
                'category' => $category,
                'cts_positions' => ['EGLL_APP'],
            ]))
            ->createQuietly(['account_id' => $student->id]);
    }

    private function createSessionForStudent(Account $student, ?\DateTimeInterface $filed = null): Session
    {
        $studentMember = Member::factory()->forAccount($student)->create();

        return Session::factory()->create([
            'student_id' => $studentMember->id,
            'filed' => $filed,
        ]);
    }
}
