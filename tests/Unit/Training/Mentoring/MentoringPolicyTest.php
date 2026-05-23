<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Mentoring;

use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\Mentoring\MentoringScope;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Policies\Training\Mentoring\MentoringPolicy;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
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
    public function view_any_denies_users_without_mentoring_access(): void
    {
        $account = Account::factory()->create();

        $this->assertFalse($this->policy->viewAny($account));
    }

    #[Test]
    public function view_allows_the_student_who_attended_the_session(): void
    {
        $student = Account::factory()->create();
        $session = $this->createSessionForStudent($student);

        $this->assertTrue($this->policy->view($student, $session));
    }

    #[Test]
    public function view_allows_the_mentor_who_conducted_the_session(): void
    {
        $mentor = Account::factory()->create();
        $mentorMember = Member::factory()->create(['id' => $mentor->id, 'cid' => $mentor->id]);
        $session = Session::factory()->create(['mentor_id' => $mentorMember->id]);

        $this->assertTrue($this->policy->view($mentor, $session));
    }

    #[Test]
    public function view_allows_a_mentor_with_permission_for_the_session_position(): void
    {
        $mentor = $this->createMentorWithPosition('EGLL_APP');
        $session = Session::factory()->create(['position' => 'EGLL_APP']);

        $this->assertTrue($this->policy->view($mentor, $session));
    }

    #[Test]
    public function view_denies_unrelated_users(): void
    {
        $account = Account::factory()->create();
        $session = Session::factory()->create(['position' => 'EGLL_APP']);

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

    private function createSessionForStudent(Account $student): Session
    {
        $studentMember = Member::factory()->create([
            'id' => $student->id,
            'cid' => $student->id,
        ]);

        return Session::factory()->create(['student_id' => $studentMember->id]);
    }
}
