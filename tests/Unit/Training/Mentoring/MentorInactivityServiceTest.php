<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Mentoring;

use App\Enums\PositionValidationStatusEnum;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Cts\PositionValidation;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Notifications\Training\Mentoring\MentoringPermissionsRemovedForInactivityNotification;
use App\Services\Training\MentorInactivityService;
use App\Services\Training\MentorPermissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MentorInactivityServiceTest extends TestCase
{
    use DatabaseTransactions;

    private MentorInactivityService $service;

    private MentorPermissionService $permissionService;

    protected function setUp(): void
    {
        parent::setUp();

        // The development databases contain historical mentoring data; clear it
        // within the transaction so each test only sees the fixtures it creates.
        MentorTrainingPosition::query()->delete();

        $this->service = app(MentorInactivityService::class);
        $this->permissionService = app(MentorPermissionService::class);
    }

    #[Test]
    public function it_flags_a_mentor_who_has_never_mentored_within_six_months_of_being_assigned(): void
    {
        $mentor = $this->createMentor();
        $this->assignMentor($mentor, 'S2 Training', ['EGTEST_TWR']);
        $this->ageMentorAssignment($mentor, now()->subMonths(6)->subDay());

        $assignments = $this->service->inactiveMentoringAssignments();

        $this->assertCount(1, $assignments);
        $this->assertSame($mentor->id, $assignments->first()['account']->id);
        $this->assertSame('S2 Training', $assignments->first()['category']);
        $this->assertNull($assignments->first()['last_mentored_at']);
    }

    #[Test]
    public function it_flags_a_mentor_whose_assignment_date_is_unknown(): void
    {
        $mentor = $this->createMentor();
        $this->assignMentor($mentor, 'S2 Training', ['EGLEGACY_TWR']);

        DB::table('mentor_training_positions')
            ->where('account_id', $mentor->id)
            ->update(['created_at' => null]);

        $assignments = $this->service->inactiveMentoringAssignments();

        $this->assertCount(1, $assignments);
        $this->assertNull($assignments->first()['assigned_at']);
    }

    #[Test]
    public function it_does_not_flag_a_newly_assigned_mentor_who_has_not_mentored_yet(): void
    {
        $mentor = $this->createMentor();
        $this->assignMentor($mentor, 'S2 Training', ['EGNEW_TWR']);

        $this->assertCount(0, $this->service->inactiveMentoringAssignments());
    }

    #[Test]
    public function it_does_not_flag_a_regranted_mentor_whose_older_sessions_are_stale(): void
    {
        $mentor = $this->createMentor();
        $this->assignMentor($mentor, 'S2 Training', ['EGREGRA_TWR']);
        $this->createMentoringSession($mentor, 'EGREGRA_TWR', now()->subMonths(9));
        $this->ageMentorAssignment($mentor, now()->subDay());

        $this->assertCount(0, $this->service->inactiveMentoringAssignments());
    }

    #[Test]
    public function it_does_not_flag_a_mentor_assigned_within_the_last_six_months_who_has_not_mentored_yet(): void
    {
        $mentor = $this->createMentor();
        $this->assignMentor($mentor, 'S2 Training', ['EGGRACE_TWR']);
        $this->ageMentorAssignment($mentor, now()->subMonths(5));

        $this->assertCount(0, $this->service->inactiveMentoringAssignments());
    }

    #[Test]
    public function it_does_not_flag_a_mentor_who_mentored_within_the_last_six_months(): void
    {
        $mentor = $this->createMentor();
        $this->assignMentor($mentor, 'S2 Training', ['EGTEST_TWR']);
        $this->createMentoringSession($mentor, 'EGTEST_TWR', now()->subMonths(5));

        $this->assertCount(0, $this->service->inactiveMentoringAssignments());
    }

    #[Test]
    public function it_flags_a_mentor_whose_last_session_was_over_six_months_ago(): void
    {
        $mentor = $this->createMentor();
        $this->assignMentor($mentor, 'S2 Training', ['EGTEST_TWR']);
        $this->ageMentorAssignment($mentor, now()->subMonths(6)->subDay());
        $this->createMentoringSession($mentor, 'EGTEST_TWR', now()->subMonths(6)->subDay());

        $assignments = $this->service->inactiveMentoringAssignments();

        $this->assertCount(1, $assignments);
        $this->assertSame('S2 Training', $assignments->first()['category']);
        $this->assertNotNull($assignments->first()['last_mentored_at']);
    }

    #[Test]
    public function it_uses_the_most_recent_session_to_determine_activity(): void
    {
        $mentor = $this->createMentor();
        $this->assignMentor($mentor, 'S2 Training', ['EGTEST_TWR']);
        $this->createMentoringSession($mentor, 'EGTEST_TWR', now()->subMonths(8));
        $this->createMentoringSession($mentor, 'EGTEST_TWR', now()->subMonth());

        $this->assertCount(0, $this->service->inactiveMentoringAssignments());
    }

    #[Test]
    public function it_only_flags_categories_in_which_the_mentor_has_not_mentored(): void
    {
        $mentor = $this->createMentor();
        $this->assignMentor($mentor, 'S2 Training', ['EGTEST_TWR']);
        $this->assignMentor($mentor, 'S3 Training', ['EGTEST_APP']);
        $this->ageMentorAssignment($mentor, now()->subMonths(6)->subDay());
        $this->createMentoringSession($mentor, 'EGTEST_APP', now()->subMonth());

        $assignments = $this->service->inactiveMentoringAssignments();

        $this->assertCount(1, $assignments);
        $this->assertSame('S2 Training', $assignments->first()['category']);
    }

    #[Test]
    public function it_does_not_review_pilot_training_groups(): void
    {
        $mentor = $this->createMentor();
        $this->assignPilotMentor($mentor, 'P1 Training', 'PPL');

        $this->assertCount(0, $this->service->inactiveMentoringAssignments());
    }

    #[Test]
    public function it_exempts_training_group_instructors(): void
    {
        $mentor = $this->createMentor();
        $this->assignMentor($mentor, 'S3 Training', ['EGTEST_APP']);

        $this->ageMentorAssignment($mentor, now()->subMonths(6)->subDay());

        Role::firstOrCreate(['name' => 'ATC APP Instructor', 'guard_name' => 'web']);
        $mentor->assignRole('ATC APP Instructor');

        $this->assertCount(0, $this->service->inactiveMentoringAssignments());
    }

    #[Test]
    public function it_exempts_other_training_staff_roles(): void
    {
        $mentor = $this->createMentor();
        $this->assignMentor($mentor, 'S2 Training', ['EGTEST_TWR']);

        $this->ageMentorAssignment($mentor, now()->subMonths(6)->subDay());

        Role::firstOrCreate(['name' => 'ATC Training Director', 'guard_name' => 'web']);
        $mentor->assignRole('ATC Training Director');

        $this->assertCount(0, $this->service->inactiveMentoringAssignments());
    }

    #[Test]
    public function it_removes_permissions_and_notifies_inactive_mentors(): void
    {
        $mentor = $this->createMentor();
        $position = $this->assignMentor($mentor, 'S2 Training', ['EGREM_TWR']);
        $this->ageMentorAssignment($mentor, now()->subMonths(6)->subDay());

        $ctsValidation = PositionValidation::query()
            ->where('member_id', $mentor->member->id)
            ->where('status', PositionValidationStatusEnum::Mentor->value)
            ->firstOrFail();

        $this->service->removeInactiveMentoringPermissions();

        $this->assertDatabaseMissing('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_id' => $position->id,
        ]);

        $this->assertDatabaseMissing('position_validations', [
            'member_id' => $mentor->member->id,
            'position_id' => $ctsValidation->position_id,
            'status' => PositionValidationStatusEnum::Mentor->value,
        ], 'cts');

        $this->assertFalse($mentor->fresh()->hasRole('ATC Mentor (TWR)'));

        Notification::assertSentTo(
            $mentor,
            MentoringPermissionsRemovedForInactivityNotification::class,
            function (MentoringPermissionsRemovedForInactivityNotification $notification, array $channels) {
                return in_array('mail', $channels, true);
            },
        );
    }

    #[Test]
    public function it_does_not_remove_permissions_or_notify_active_mentors(): void
    {
        $mentor = $this->createMentor();
        $position = $this->assignMentor($mentor, 'S2 Training', ['EGACTIVE_TWR']);
        $this->createMentoringSession($mentor, 'EGACTIVE_TWR', now()->subMonth());

        $this->service->removeInactiveMentoringPermissions();

        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_id' => $position->id,
        ]);

        Notification::assertNotSentTo($mentor, MentoringPermissionsRemovedForInactivityNotification::class);
    }

    #[Test]
    public function it_does_not_remove_permissions_from_exempt_staff(): void
    {
        $mentor = $this->createMentor();
        $position = $this->assignMentor($mentor, 'S3 Training', ['EGSTAFF_APP']);

        $this->ageMentorAssignment($mentor, now()->subMonths(6)->subDay());

        Role::firstOrCreate(['name' => 'ATC APP Instructor', 'guard_name' => 'web']);
        $mentor->assignRole('ATC APP Instructor');

        $this->service->removeInactiveMentoringPermissions();

        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_id' => $position->id,
        ]);

        Notification::assertNotSentTo($mentor, MentoringPermissionsRemovedForInactivityNotification::class);
    }

    /**
     * Backdate the mentoring rights the mentor currently holds, as the inactivity
     * grace period is measured from the date they were assigned those rights.
     */
    private function ageMentorAssignment(Account $mentor, \Carbon\Carbon $assignedAt): void
    {
        MentorTrainingPosition::query()
            ->where('account_id', $mentor->id)
            ->update(['created_at' => $assignedAt]);
    }

    private function createMentor(): Account
    {
        $account = Account::factory()->create();

        Member::factory()->forAccount($account)->create();

        return $account->fresh();
    }

    /**
     * @param  array<int, string>  $callsigns
     */
    private function assignMentor(Account $mentor, string $category, array $callsigns): TrainingPosition
    {
        foreach ($callsigns as $callsign) {
            CtsPosition::firstOrCreate(['callsign' => $callsign]);
        }

        $position = TrainingPosition::factory()->create([
            'category' => $category,
            'cts_positions' => $callsigns,
        ]);

        $this->permissionService->assignToMentorable($mentor, $position, Account::factory()->create(), $category);

        return $position;
    }

    private function assignPilotMentor(Account $mentor, string $category, string $code): Qualification
    {
        $callsign = MentorPermissionService::QUALIFICATION_CTS_MENTOR_POSITION_MAP[$code]
            ?? MentorPermissionService::QUALIFICATION_CTS_POSITION_MAP[$code];

        CtsPosition::firstOrCreate(['callsign' => $callsign]);

        $qualification = Qualification::firstWhere('code', $code) ?? Qualification::factory()->create([
            'code' => $code,
            'type' => $code === 'TFP' ? 'pilot_virtual' : 'pilot',
            'vatsim' => 0,
        ]);

        $this->permissionService->assignToMentorable($mentor, $qualification, Account::factory()->create(), $category);

        return $qualification;
    }

    private function createMentoringSession(Account $mentor, string $callsign, \Carbon\Carbon $takenAt): Session
    {
        $member = Member::query()->where('cid', $mentor->id)->firstOrFail();

        return Session::factory()->create([
            'mentor_id' => $member->id,
            'position' => $callsign,
            'taken' => 1,
            'session_done' => 1,
            'cancelled_datetime' => null,
            'taken_date' => $takenAt->toDateString(),
        ]);
    }
}
