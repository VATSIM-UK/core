<?php

namespace Tests\Unit\Jobs\Training;

use App\Jobs\Training\CheckAvailability;
use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\AvailabilityCheck;
use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Notifications\Training\AvailabilityWarningCreated;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
            'status' => 'failed',
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
            'status' => 'failed',
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
            'status' => 'failed',
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
            ->where('status', 'failed')
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
            ->where('status', 'failed')
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
            'status' => 'failed',
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
            ->where('status', 'failed')
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

        // Assert: Failed check and warning should exist
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => 'failed',
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
            'status' => 'passed',
        ]);

        // Assert: The warning should still be pending (job doesn't resolve it)
        $this->assertDatabaseHas('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => 'pending',
        ]);
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
            'status' => 'passed',
        ]);

        // Assert: No availability warning should be created
        $this->assertDatabaseMissing('availability_warnings', [
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
            'status' => 'passed',
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
            'status' => 'failed',
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
            'status' => 'failed',
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
            'status' => 'failed',
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
            'status' => 'failed',
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
            'status' => 'failed',
        ]);

        // Assert: An availability warning should be created
        $this->assertDatabaseHas('availability_warnings', [
            'training_place_id' => $this->trainingPlace->id,
        ]);
    }
}
