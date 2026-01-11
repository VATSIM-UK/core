<?php

namespace Tests\Unit\Jobs\Training;

use App\Jobs\Training\CheckAvailability;
use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\AvailabilityCheck;
use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckAvailabilityTest extends TestCase
{
    use DatabaseTransactions;

    private Account $account;

    private Member $ctsMember;

    private TrainingPlace $trainingPlace;

    protected function setUp(): void
    {
        parent::setUp();

        // Create CTS member first as the CID is not overwritten when using a factory
        $this->ctsMember = Member::factory()->create();
        $this->account = Account::factory()->create(['id' => $this->ctsMember->cid]);

        // Create a waiting list and add the account to it
        $waitingList = WaitingList::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($this->account, Account::factory()->create());

        // Create a training place for this waiting list account
        $this->trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
        ]);
    }

    #[Test]
    public function it_creates_passed_availability_check_when_availability_exists(): void
    {
        // Arrange: Create availability for the student
        Availability::factory()->forStudent($this->ctsMember->id)->create();

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
    public function it_creates_passed_availability_check_when_multiple_availabilities_exist(): void
    {
        // Arrange: Create multiple availabilities for the student
        Availability::factory()->forStudent($this->ctsMember->id)->count(3)->create();

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

        // Assert: The warning should expire in 5 days
        $warning = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)->first();
        $this->assertNotNull($warning);
        $this->assertEqualsWithDelta(
            now()->addDays(5)->timestamp,
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
        // Arrange & Act: First run without availability
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

        // Arrange: Now create availability
        Availability::factory()->forStudent($this->ctsMember->id)->create();

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
}
