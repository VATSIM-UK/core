<?php

namespace Tests\Unit\Jobs\Training;

use App\Enums\AvailabilityCheckStatus;
use App\Jobs\Training\ActionExpiredAvailabilityWarningRemoval;
use App\Jobs\Training\ActionFourthAvailabilityFailureRemoval;
use App\Jobs\Training\CheckAvailability;
use App\Models\Atc\Position as AtcPosition;
use App\Models\Cts\Availability;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\AvailabilityCheck;
use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPlace\TrainingPlaceLeaveOfAbsence;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Notifications\Training\AvailabilityWarningCreated;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckAvailabilityTest extends TestCase
{
    use DatabaseTransactions;

    private Account $account;

    private Member $ctsMember;

    private TrainingPlace $trainingPlace;

    private TrainingPosition $trainingPosition;

    protected function setUp(): void
    {
        parent::setUp();

        // Create CTS member first as the CID is not overwritten when using a factory
        $this->ctsMember = Member::factory()->create();
        $this->account = Account::factory()->create(['id' => $this->ctsMember->cid]);

        // Create a waiting list and add the account to it
        $waitingList = WaitingList::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($this->account, Account::factory()->create());

        // Create a training position with CTS positions
        $this->trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => ['EGLL_APP', 'EGLL_TWR'],
        ]);

        // Create a training place for this waiting list account
        $this->trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $this->trainingPosition->id,
        ]);

        $this->trainingPlace->forceFill([
            'created_at' => now()->subHours(TrainingPlace::AVAILABILITY_CHECK_GRACE_PERIOD_HOURS + 1),
        ])->saveQuietly();
    }

    #[Test]
    public function it_creates_on_leave_availability_check_and_sends_no_warning_when_on_leave_of_absence(): void
    {
        // Arrange: Training place has a current leave of absence
        TrainingPlaceLeaveOfAbsence::create([
            'training_place_id' => $this->trainingPlace->id,
            'begins_at' => now()->subDay(),
            'ends_at' => now()->addDays(7),
            'reason' => 'Annual leave',
        ]);

        Notification::fake();

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: An availability check with status on leave is created
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::OnLeave->value,
        ]);

        // Assert: No availability warning is created
        $this->assertDatabaseMissing('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);

        // Assert: No availability warning notification is sent
        Notification::assertNothingSent();
    }

    #[Test]
    public function it_does_not_create_availability_check_when_within_grace_period_after_creation(): void
    {
        Notification::fake();

        $this->trainingPlace->forceFill(['created_at' => now()])->saveQuietly();

        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        $this->assertDatabaseMissing('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
        Notification::assertNothingSent();
    }

    #[Test]
    public function it_still_records_on_leave_check_when_within_grace_period_after_creation(): void
    {
        $this->trainingPlace->forceFill(['created_at' => now()])->saveQuietly();

        TrainingPlaceLeaveOfAbsence::create([
            'training_place_id' => $this->trainingPlace->id,
            'begins_at' => now()->subDay(),
            'ends_at' => now()->addDays(7),
            'reason' => 'Annual leave',
        ]);

        Notification::fake();

        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::OnLeave->value,
        ]);
        Notification::assertNothingSent();
    }

    #[Test]
    public function it_creates_failed_availability_check_when_only_availability_exists(): void
    {
        // Arrange: Create availability for the student but no session request
        Availability::factory()->forStudent($this->ctsMember->id)->create();

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A failed availability check should be created (because no session exists)
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Failed->value,
        ]);

        // Assert: An availability warning should be created
        $this->assertDatabaseHas('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
    }

    #[Test]
    public function it_creates_failed_availability_check_when_multiple_availabilities_exist_but_no_session(): void
    {
        // Arrange: Create multiple availabilities for the student but no session request
        Availability::factory()->forStudent($this->ctsMember->id)->count(3)->create();

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A failed availability check should be created (because no session exists)
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Failed->value,
        ]);

        // Assert: An availability warning should be created
        $this->assertDatabaseHas('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
    }

    #[Test]
    public function it_creates_failed_availability_check_when_no_availability_exists(): void
    {
        // Arrange: No availability records exist for the student

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A failed availability check should be created
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Failed->value,
        ]);
    }

    #[Test]
    public function it_creates_availability_warning_when_no_availability_exists_and_no_pending_warning(): void
    {
        // Arrange: No availability records exist for the student

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: An availability warning should be created
        $this->assertDatabaseHas('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => 'pending',
        ]);

        // Assert: The warning should expire at the end of day 5 days from now
        $warning = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)->first();
        $this->assertNotNull($warning);
        $this->assertEqualsWithDelta(
            now()->addDays(5)->endOfDay()->timestamp,
            $warning->expires_at->timestamp,
            60 // Allow 60 seconds delta for test execution time
        );
    }

    #[Test]
    public function it_links_availability_warning_to_failed_check(): void
    {
        // Arrange: No availability records exist for the student

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: The warning should be linked to the failed check
        $failedCheck = AvailabilityCheck::where('training_place_id', $this->trainingPlace->id)
            ->where('status', AvailabilityCheckStatus::Failed)
            ->first();

        $warning = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)->first();

        $this->assertNotNull($failedCheck);
        $this->assertNotNull($warning);
        $this->assertEquals($failedCheck->id, $warning->availability_check_id);
    }

    #[Test]
    public function it_does_not_create_new_warning_when_pending_warning_already_exists(): void
    {
        // Arrange: Create an existing pending warning
        $existingCheck = AvailabilityCheck::factory()->failed()->create([
            'training_place_id' => $this->trainingPlace->id,
        ]);

        AvailabilityWarning::factory()->pending()->create([
            'training_place_id' => $this->trainingPlace->id,
            'availability_check_id' => $existingCheck->id,
        ]);

        // Act: Run the job again (still no availability)
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: Only one pending warning should exist
        $pendingWarnings = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)
            ->where('status', 'pending')
            ->get();

        $this->assertCount(1, $pendingWarnings);

        // Assert: A new failed check should still be created
        $failedChecks = AvailabilityCheck::where('training_place_id', $this->trainingPlace->id)
            ->where('status', AvailabilityCheckStatus::Failed)
            ->get();

        $this->assertCount(2, $failedChecks); // The existing one + the new one
    }

    #[Test]
    public function it_creates_new_warning_when_previous_warning_was_resolved(): void
    {
        // Arrange: Create an existing resolved warning
        $existingCheck = AvailabilityCheck::factory()->failed()->create([
            'training_place_id' => $this->trainingPlace->id,
        ]);

        AvailabilityWarning::factory()->resolved()->create([
            'training_place_id' => $this->trainingPlace->id,
            'availability_check_id' => $existingCheck->id,
        ]);

        // Act: Run the job again (still no availability)
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A new pending warning should be created
        $pendingWarnings = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)
            ->where('status', 'pending')
            ->get();

        $this->assertCount(1, $pendingWarnings);

        // Assert: The resolved warning should still exist
        $resolvedWarnings = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)
            ->where('status', 'resolved')
            ->get();

        $this->assertCount(1, $resolvedWarnings);
    }

    #[Test]
    public function it_creates_new_warning_when_previous_warning_was_expired(): void
    {
        // Arrange: Create an existing expired warning
        $existingCheck = AvailabilityCheck::factory()->failed()->create([
            'training_place_id' => $this->trainingPlace->id,
        ]);

        AvailabilityWarning::factory()->expired()->create([
            'training_place_id' => $this->trainingPlace->id,
            'availability_check_id' => $existingCheck->id,
        ]);

        // Act: Run the job again (still no availability)
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A new pending warning should be created
        $pendingWarnings = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)
            ->where('status', 'pending')
            ->get();

        $this->assertCount(1, $pendingWarnings);

        // Assert: The expired warning should still exist
        $expiredWarnings = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)
            ->where('status', 'expired')
            ->get();

        $this->assertCount(1, $expiredWarnings);
    }

    #[Test]
    public function it_removes_training_place_immediately_on_fourth_availability_failure_after_three_resolved_warnings(): void
    {
        Bus::fake();
        Notification::fake();

        // Arrange: Create 3 resolved warnings (member failed then passed within window on 3 previous occasions)
        for ($i = 0; $i < 3; $i++) {
            $failedCheck = AvailabilityCheck::factory()->failed()->create([
                'training_place_id' => $this->trainingPlace->id,
            ]);
            AvailabilityWarning::factory()->resolved()->create([
                'training_place_id' => $this->trainingPlace->id,
                'availability_check_id' => $failedCheck->id,
            ]);
        }

        // Act: Run the job with failure conditions (no availability) - 4th failure
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A failed check was recorded
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Failed->value,
        ]);

        // Assert: A 4th warning was created with expires_at = now() (immediate removal)
        $fourthWarning = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)
            ->where('status', 'pending')
            ->latest()
            ->first();
        $this->assertNotNull($fourthWarning);
        $this->assertFalse($fourthWarning->expires_at->isFuture(), 'Fourth failure warning should have expires_at in the past for immediate removal');

        // Assert: Fourth-failure removal job was dispatched for the 4th warning
        Bus::assertDispatched(ActionFourthAvailabilityFailureRemoval::class, function ($dispatchedJob) use ($fourthWarning) {
            return $dispatchedJob->availabilityWarning->id === $fourthWarning->id;
        });
        Bus::assertNotDispatched(ActionExpiredAvailabilityWarningRemoval::class);

        // Assert: No "warning created" notification (job only sends it for normal 5-day warnings, not 4th failure)
        Notification::assertNotSentTo($this->account, AvailabilityWarningCreated::class);
    }

    #[Test]
    public function it_creates_normal_warning_when_only_two_previous_warnings_were_resolved(): void
    {
        Bus::fake();

        // Arrange: Create 2 resolved warnings (not yet at the 3-strike threshold)
        for ($i = 0; $i < 2; $i++) {
            $failedCheck = AvailabilityCheck::factory()->failed()->create([
                'training_place_id' => $this->trainingPlace->id,
            ]);
            AvailabilityWarning::factory()->resolved()->create([
                'training_place_id' => $this->trainingPlace->id,
                'availability_check_id' => $failedCheck->id,
            ]);
        }

        // Act: Run the job with failure conditions
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A normal 5-day warning was created (not immediate removal)
        $thirdWarning = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)
            ->where('status', 'pending')
            ->latest()
            ->first();
        $this->assertNotNull($thirdWarning);
        $this->assertTrue($thirdWarning->expires_at->isFuture(), 'Third failure should still get the normal 5-day window');

        Bus::assertNotDispatched(ActionExpiredAvailabilityWarningRemoval::class);
        Bus::assertNotDispatched(ActionFourthAvailabilityFailureRemoval::class);
    }

    #[Test]
    public function it_only_checks_availability_for_specific_student(): void
    {
        // Arrange: Create availability for a different student
        $otherMember = Member::factory()->create();
        Availability::factory()->forStudent($otherMember->id)->create();

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: Should create a failed check because the availability is for a different student
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Failed->value,
        ]);

        // Assert: An availability warning should be created
        $this->assertDatabaseHas('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => 'pending',
        ]);
    }

    #[Test]
    public function it_can_handle_multiple_checks_for_same_training_place(): void
    {
        // Arrange & Act: Run the job twice without availability
        $job1 = new CheckAvailability($this->trainingPlace);
        $job1->handle();

        $job2 = new CheckAvailability($this->trainingPlace);
        $job2->handle();

        // Assert: Two failed checks should be created
        $failedChecks = AvailabilityCheck::where('training_place_id', $this->trainingPlace->id)
            ->where('status', AvailabilityCheckStatus::Failed)
            ->get();

        $this->assertCount(2, $failedChecks);

        // Assert: Only one pending warning should exist
        $pendingWarnings = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)
            ->where('status', 'pending')
            ->get();

        $this->assertCount(1, $pendingWarnings);
    }

    #[Test]
    public function it_can_transition_from_failed_to_passed_checks(): void
    {
        // Arrange & Act: First run without availability or session
        $job1 = new CheckAvailability($this->trainingPlace);
        $job1->handle();

        // Assert: Failed check and pending warning should exist
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Failed->value,
        ]);

        $this->assertDatabaseHas('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => 'pending',
        ]);

        // Arrange: Now create both availability and session
        Availability::factory()->forStudent($this->ctsMember->id)->create();
        Session::factory()->create([
            'student_id' => $this->ctsMember->id,
            'position' => 'EGLL_APP',
        ]);

        // Act: Run the job again
        $job2 = new CheckAvailability($this->trainingPlace);
        $job2->handle();

        // Assert: A passed check should now exist
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Passed->value,
        ]);

        // Assert: The existing pending warning should be marked resolved and linked to the passed check
        $passedCheck = AvailabilityCheck::where('training_place_id', $this->trainingPlace->id)
            ->where('status', AvailabilityCheckStatus::Passed)
            ->first();
        $this->assertNotNull($passedCheck);

        $resolvedWarning = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)
            ->where('status', 'resolved')
            ->first();
        $this->assertNotNull($resolvedWarning, 'Job should resolve the pending warning when check passes');
        $this->assertEquals($passedCheck->id, $resolvedWarning->resolved_availability_check_id);
    }

    #[Test]
    public function it_sends_notification_when_availability_warning_is_created(): void
    {
        // Arrange: Fake the notification system
        Notification::fake();

        // Act: Run the job without availability (which creates a warning)
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: The account should receive the notification
        Notification::assertSentTo(
            $this->account,
            AvailabilityWarningCreated::class,
            function ($notification, $channels) {
                // Verify the notification is sent via email
                $this->assertContains('mail', $channels);

                // Verify the notification has the correct warning attached
                $this->assertInstanceOf(AvailabilityWarning::class, $notification->availabilityWarning);
                $this->assertEquals($this->trainingPlace->id, $notification->availabilityWarning->training_place_id);

                return true;
            }
        );
    }

    #[Test]
    public function it_does_not_send_notification_when_check_passes(): void
    {
        // Arrange: Fake the notification system and create availability and session
        Notification::fake();
        Availability::factory()->forStudent($this->ctsMember->id)->create();
        Session::factory()->create([
            'student_id' => $this->ctsMember->id,
            'position' => 'EGLL_APP',
        ]);

        // Act: Run the job with availability and session (which passes)
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: No notification should be sent
        Notification::assertNothingSent();
    }

    #[Test]
    public function it_sends_notification_atomically_with_database_creation(): void
    {
        // Arrange: Fake the notification system
        Notification::fake();

        // Act: Run the job without availability
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: Both the warning and notification should exist
        $warning = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)
            ->where('status', 'pending')
            ->first();

        $this->assertNotNull($warning, 'Warning should be created in database');

        Notification::assertSentTo(
            $this->account,
            AvailabilityWarningCreated::class,
            function ($notification) use ($warning) {
                // The notification should reference the same warning that was created
                return $notification->availabilityWarning->id === $warning->id;
            }
        );
    }

    #[Test]
    public function it_creates_passed_check_when_both_availability_and_session_exist(): void
    {
        // Arrange: Create availability and session for the student
        Availability::factory()->forStudent($this->ctsMember->id)->create();
        Session::factory()->create([
            'student_id' => $this->ctsMember->id,
            'position' => 'EGLL_APP', // Matches one of the cts_positions
        ]);

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A passed availability check should be created
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Passed->value,
        ]);

        // Assert: No availability warning should be created
        $this->assertDatabaseMissing('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
    }

    #[Test]
    public function it_creates_passed_check_when_member_has_pending_exam(): void
    {
        // Arrange: No availability or session, but member has a pending (unfinished) exam booking
        // hasPendingExam matches on position_1 vs training position's exam_callsign (or position->callsign)
        $this->trainingPosition->update(['exam_callsign' => 'EGLL_APP']);
        ExamBooking::factory()->create([
            'student_id' => $this->ctsMember->id,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'position_1' => 'EGLL_APP',
        ]);
        $this->trainingPlace->unsetRelation('trainingPosition');

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A passed availability check should be created
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Passed->value,
        ]);

        // Assert: No availability warning should be created
        $this->assertDatabaseMissing('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
    }

    #[Test]
    public function it_resolves_pending_warning_when_check_passes_due_to_pending_exam(): void
    {
        // Arrange: Existing pending warning and a pending exam (no availability/session)
        $this->trainingPosition->update(['exam_callsign' => 'EGLL_APP']);
        $existingCheck = AvailabilityCheck::factory()->failed()->create([
            'training_place_id' => $this->trainingPlace->id,
        ]);
        AvailabilityWarning::factory()->pending()->create([
            'training_place_id' => $this->trainingPlace->id,
            'availability_check_id' => $existingCheck->id,
        ]);
        ExamBooking::factory()->create([
            'student_id' => $this->ctsMember->id,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'position_1' => 'EGLL_APP',
        ]);
        $this->trainingPlace->unsetRelation('trainingPosition');

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A passed check was created
        $passedCheck = AvailabilityCheck::where('training_place_id', $this->trainingPlace->id)
            ->where('status', AvailabilityCheckStatus::Passed)
            ->first();
        $this->assertNotNull($passedCheck);

        // Assert: The pending warning was resolved and linked to the passed check
        $resolvedWarning = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)
            ->where('status', 'resolved')
            ->first();
        $this->assertNotNull($resolvedWarning, 'Job should resolve the pending warning when check passes due to pending exam');
        $this->assertEquals($passedCheck->id, $resolvedWarning->resolved_availability_check_id);
    }

    #[Test]
    public function it_does_not_send_notification_when_check_passes_due_to_pending_exam(): void
    {
        // Arrange: Pending exam, no availability or session; fake notifications
        $this->trainingPosition->update(['exam_callsign' => 'EGLL_APP']);
        Notification::fake();
        ExamBooking::factory()->create([
            'student_id' => $this->ctsMember->id,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'position_1' => 'EGLL_APP',
        ]);
        // Clear cached relation so job loads training position with updated exam_callsign (observer may have loaded it at create)
        $this->trainingPlace->unsetRelation('trainingPosition');

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: No notification should be sent (check passed due to pending exam)
        Notification::assertNothingSent();
    }

    #[Test]
    public function it_creates_failed_check_when_member_has_pending_exam_for_different_position(): void
    {
        // Arrange: Pending exam exists but position_1 does not match training position's exam_callsign
        $this->trainingPosition->update(['exam_callsign' => 'EGLL_APP']);
        ExamBooking::factory()->create([
            'student_id' => $this->ctsMember->id,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'position_1' => 'EGKK_TWR', // Different position; hasPendingExam checks position_1
        ]);
        $this->trainingPlace->unsetRelation('trainingPosition');

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: Check fails (pending exam is for a different position)
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Failed->value,
        ]);
        $this->assertDatabaseHas('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
    }

    #[Test]
    public function it_creates_passed_check_when_member_has_pending_exam_and_exam_callsign_falls_back_to_position(): void
    {
        // Arrange: exam_callsign is not set; hasPendingExam falls back to training position's position->callsign
        $atcPosition = AtcPosition::factory()->create(['callsign' => 'EGLL_TWR']);
        $this->trainingPosition->update([
            'position_id' => $atcPosition->id,
            'exam_callsign' => null,
        ]);
        ExamBooking::factory()->create([
            'student_id' => $this->ctsMember->id,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'position_1' => 'EGLL_TWR',
        ]);
        $this->trainingPlace->unsetRelation('trainingPosition');

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: Passed check (pending exam matched via position->callsign fallback)
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Passed->value,
        ]);
        $this->assertDatabaseMissing('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
    }

    #[Test]
    public function it_creates_failed_check_when_exam_callsign_falls_back_to_position_but_exam_position_does_not_match(): void
    {
        // Arrange: exam_callsign not set (fallback to position->callsign), but exam is for a different position
        $atcPosition = AtcPosition::factory()->create(['callsign' => 'EGLL_TWR']);
        $this->trainingPosition->update([
            'position_id' => $atcPosition->id,
            'exam_callsign' => null,
        ]);
        ExamBooking::factory()->create([
            'student_id' => $this->ctsMember->id,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'position_1' => 'EGKK_APP', // Does not match position->callsign (EGLL_TWR)
        ]);
        $this->trainingPlace->unsetRelation('trainingPosition');

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: Check fails (pending exam position_1 does not match fallback position->callsign)
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Failed->value,
        ]);
        $this->assertDatabaseHas('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
    }

    #[Test]
    public function it_creates_passed_check_when_session_matches_any_cts_position(): void
    {
        // Arrange: Create availability and session with second callsign
        Availability::factory()->forStudent($this->ctsMember->id)->create();
        Session::factory()->create([
            'student_id' => $this->ctsMember->id,
            'position' => 'EGLL_TWR', // Matches the second cts_position
        ]);

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A passed availability check should be created
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Passed->value,
        ]);

        // Assert: No availability warning should be created
        $this->assertDatabaseMissing('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
    }

    #[Test]
    public function it_creates_failed_check_when_session_exists_but_no_availability(): void
    {
        // Arrange: Create session but no availability
        Session::factory()->create([
            'student_id' => $this->ctsMember->id,
            'position' => 'EGLL_APP',
        ]);

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A failed availability check should be created
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Failed->value,
        ]);

        // Assert: An availability warning should be created
        $this->assertDatabaseHas('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
    }

    #[Test]
    public function it_creates_failed_check_when_session_position_does_not_match_cts_positions(): void
    {
        // Arrange: Create availability and session with non-matching position
        Availability::factory()->forStudent($this->ctsMember->id)->create();
        Session::factory()->create([
            'student_id' => $this->ctsMember->id,
            'position' => 'EGKK_APP', // Does not match any cts_position
        ]);

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A failed availability check should be created
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Failed->value,
        ]);

        // Assert: An availability warning should be created
        $this->assertDatabaseHas('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
    }

    #[Test]
    public function it_creates_failed_check_when_session_exists_for_different_student(): void
    {
        // Arrange: Create availability for the student and session for a different student
        Availability::factory()->forStudent($this->ctsMember->id)->create();
        $otherMember = Member::factory()->create();
        Session::factory()->create([
            'student_id' => $otherMember->id,
            'position' => 'EGLL_APP',
        ]);

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A failed availability check should be created
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Failed->value,
        ]);

        // Assert: An availability warning should be created
        $this->assertDatabaseHas('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
    }

    #[Test]
    public function it_creates_failed_check_when_training_position_has_no_cts_positions(): void
    {
        // Arrange: Update training position to have no cts_positions
        $this->trainingPosition->update(['cts_positions' => null]);
        Availability::factory()->forStudent($this->ctsMember->id)->create();

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A failed availability check should be created
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Failed->value,
        ]);

        // Assert: An availability warning should be created
        $this->assertDatabaseHas('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
    }

    #[Test]
    public function it_creates_failed_check_when_training_place_has_no_training_position(): void
    {
        // Arrange: Update training place to have no training position
        $this->trainingPlace->update(['training_position_id' => null]);
        Availability::factory()->forStudent($this->ctsMember->id)->create();

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A failed availability check should be created
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Failed->value,
        ]);

        // Assert: An availability warning should be created
        $this->assertDatabaseHas('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
    }

    #[Test]
    public function it_creates_passed_check_when_session_exists_but_has_been_taken(): void
    {
        // Arrange: Create availability and a session that has already been taken (taken_time set)
        Availability::factory()->forStudent($this->ctsMember->id)->create();
        Session::factory()->accepted()->create([
            'student_id' => $this->ctsMember->id,
            'position' => 'EGLL_APP',
        ]);

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A failed availability check should be created (untaken session request required)
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Passed->value,
        ]);
    }

    #[Test]
    public function it_creates_passed_check_when_pending_and_taken_sessions_exist_for_same_position(): void
    {
        // Arrange: Create availability and two sessions for the same position:
        // one pending (no taken_time) and one already taken (taken_time set).
        Availability::factory()->forStudent($this->ctsMember->id)->create();

        // Pending session – should satisfy the session request requirement.
        Session::factory()->create([
            'student_id' => $this->ctsMember->id,
            'position' => 'EGLL_APP',
        ]);

        // Completed session for the same position – should not prevent the check from passing.
        Session::factory()->accepted()->create([
            'student_id' => $this->ctsMember->id,
            'position' => 'EGLL_APP',
        ]);

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: A passed availability check should be created because there is at least one pending session
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Passed->value,
        ]);

        // Assert: No availability warning should be created when the check passes
        $this->assertDatabaseMissing('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
    }

    #[Test]
    public function it_treats_future_taken_session_with_session_done_zero_as_pending(): void
    {
        // Arrange: Availability and a future session with taken_time set and session_done = 0
        Availability::factory()->forStudent($this->ctsMember->id)->create();
        Session::factory()->create([
            'student_id' => $this->ctsMember->id,
            'position' => 'EGLL_APP',
            'taken_time' => now()->addDay(),
            'taken_date' => now()->addDay(),
            'session_done' => 0,
        ]);

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: Check passes because hasPendingSession matches future taken_date/session_done = 0
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Passed->value,
        ]);
        $this->assertDatabaseMissing('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
    }

    #[Test]
    public function it_does_not_treat_past_taken_session_as_pending_even_with_session_done_zero(): void
    {
        // Arrange: Availability and a past session with taken_time set and session_done = 0
        Availability::factory()->forStudent($this->ctsMember->id)->create();
        Session::factory()->create([
            'student_id' => $this->ctsMember->id,
            'position' => 'EGLL_APP',
            'taken_time' => now()->subDay(),
            'taken_date' => now()->subDay(),
            'session_done' => 0,
        ]);

        // Act: Run the job
        $job = new CheckAvailability($this->trainingPlace);
        $job->handle();

        // Assert: Check fails because hasPendingSession excludes past taken_date
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Failed->value,
        ]);
        $this->assertDatabaseHas('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
    }
}
