<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands\Training;

use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Notifications\Training\Mentoring\MentoringPermissionsRemovedForInactivityNotification;
use App\Services\Training\MentorPermissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RemoveInactiveMentoringPermissionsCommandTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // The development databases contain historical mentoring data; clear it
        // within the transaction so each test only sees the fixtures it creates.
        MentorTrainingPosition::query()->delete();
    }

    #[Test]
    public function it_removes_permissions_and_notifies_inactive_mentors(): void
    {
        $mentor = $this->createMentor();
        $position = $this->assignMentor($mentor, 'S2 Training', ['EGCMDTWR_TWR']);
        $this->ageMentorAssignment($mentor, now()->subMonths(6)->subDay());

        $this->artisan('training:remove-inactive-mentoring-permissions')->assertExitCode(0);

        $this->assertDatabaseMissing('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_id' => $position->id,
        ]);

        Notification::assertSentTo($mentor, MentoringPermissionsRemovedForInactivityNotification::class);
    }

    #[Test]
    public function it_does_not_touch_active_mentors(): void
    {
        $mentor = $this->createMentor();
        $position = $this->assignMentor($mentor, 'S2 Training', ['EGCMDACT_TWR']);
        $this->createMentoringSession($mentor, 'EGCMDACT_TWR', now()->subMonth());

        $this->artisan('training:remove-inactive-mentoring-permissions')->assertExitCode(0);

        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_id' => $position->id,
        ]);

        Notification::assertNotSentTo($mentor, MentoringPermissionsRemovedForInactivityNotification::class);
    }

    #[Test]
    public function it_does_not_touch_newly_assigned_mentors_without_sessions(): void
    {
        $mentor = $this->createMentor();
        $position = $this->assignMentor($mentor, 'S2 Training', ['EGCMDNEW_TWR']);

        $this->artisan('training:remove-inactive-mentoring-permissions')->assertExitCode(0);

        $this->assertDatabaseHas('mentor_training_positions', [
            'account_id' => $mentor->id,
            'mentorable_id' => $position->id,
        ]);

        Notification::assertNotSentTo($mentor, MentoringPermissionsRemovedForInactivityNotification::class);
    }

    #[Test]
    public function it_does_not_touch_exempt_training_staff(): void
    {
        $mentor = $this->createMentor();
        $position = $this->assignMentor($mentor, 'S3 Training', ['EGCMDSTF_APP']);
        $this->ageMentorAssignment($mentor, now()->subMonths(6)->subDay());

        Role::firstOrCreate(['name' => 'ATC Training Manager', 'guard_name' => 'web']);
        $mentor->assignRole('ATC Training Manager');

        $this->artisan('training:remove-inactive-mentoring-permissions')->assertExitCode(0);

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

        app(MentorPermissionService::class)->assignToMentorable(
            $mentor,
            $position,
            Account::factory()->create(),
            $category,
        );

        return $position;
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
