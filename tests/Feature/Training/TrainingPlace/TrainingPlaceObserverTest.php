<?php

namespace Tests\Feature\Training\TrainingPlace;

use App\Enums\PositionValidationStatusEnum;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingPlaceObserverTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_automatically_assigns_mentoring_permissions_when_training_place_is_created(): void
    {
        // Arrange: Create the necessary data
        $ctsPosition = CtsPosition::factory()->create();
        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => [$ctsPosition->callsign],
        ]);

        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();

        // Create CTS member for the student
        Member::factory()->create(['cid' => $student->id]);

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        // Act: Create a training place (this should trigger the observer)
        TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        // Assert: The mentoring permissions should have been automatically assigned
        $this->assertDatabaseHas('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition->id,
            'status' => PositionValidationStatusEnum::Student->value,
            'changed_by' => $student->id,
        ], 'cts');
    }

    #[Test]
    public function it_assigns_mentoring_permissions_for_multiple_cts_positions(): void
    {
        // Arrange: Create multiple CTS positions
        $ctsPosition1 = CtsPosition::factory()->create(['callsign' => 'EGKK_TWR']);
        $ctsPosition2 = CtsPosition::factory()->create(['callsign' => 'EGLL_TWR']);

        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => [$ctsPosition1->callsign, $ctsPosition2->callsign],
        ]);

        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();

        // Create CTS member for the student
        Member::factory()->create(['cid' => $student->id]);

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        // Act: Create a training place
        TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        // Assert: Mentoring permissions should be assigned for both positions
        $this->assertDatabaseHas('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition1->id,
            'status' => PositionValidationStatusEnum::Student->value,
        ], 'cts');

        $this->assertDatabaseHas('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition2->id,
            'status' => PositionValidationStatusEnum::Student->value,
        ], 'cts');
    }

    #[Test]
    public function it_handles_training_place_creation_when_student_has_no_cts_member(): void
    {
        // Arrange: Create data without a CTS member
        $ctsPosition = CtsPosition::factory()->create(['callsign' => 'EGKK_TWR']);
        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => [$ctsPosition->callsign],
        ]);

        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();

        // Do NOT create a CTS member for the student

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        // Act: Create a training place (should not throw an exception)
        TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        // Assert: No position validations should be created
        $this->assertDatabaseMissing('position_validations', [
            'position_id' => $ctsPosition->id,
        ], 'cts');
    }

    #[Test]
    public function it_records_correct_changed_by_and_date_changed_values(): void
    {
        // Arrange
        $ctsPosition = CtsPosition::factory()->create();
        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => [$ctsPosition->callsign],
        ]);

        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        $beforeCreation = now();

        // Act
        TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        $afterCreation = now();

        // Assert: Check that changed_by is the student's ID and date_changed is recent
        $this->assertDatabaseHas('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition->id,
            'changed_by' => $student->id,
        ], 'cts');

        // Verify the date_changed is within the expected timeframe
        $validation = \App\Models\Cts\PositionValidation::where('member_id', $student->member->id)
            ->where('position_id', $ctsPosition->id)
            ->first();

        $this->assertNotNull($validation);
        $this->assertTrue(
            $validation->date_changed >= $beforeCreation &&
            $validation->date_changed <= $afterCreation,
            'date_changed should be set to the current timestamp'
        );
    }

    #[Test]
    public function it_automatically_revokes_mentoring_permissions_when_training_place_is_deleted(): void
    {
        // Arrange: Create a training place with permissions
        $ctsPosition = CtsPosition::factory()->create();
        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => [$ctsPosition->callsign],
        ]);

        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        $trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        // Verify permissions were created
        $this->assertDatabaseHas('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition->id,
            'status' => PositionValidationStatusEnum::Student->value,
        ], 'cts');

        // Act: Delete the training place (soft delete)
        $trainingPlace->delete();

        // Assert: The mentoring permissions should have been automatically revoked
        $this->assertDatabaseMissing('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition->id,
        ], 'cts');
    }

    #[Test]
    public function it_revokes_mentoring_permissions_for_multiple_cts_positions_on_deletion(): void
    {
        // Arrange: Create multiple CTS positions with unique callsigns
        $ctsPosition1 = CtsPosition::factory()->create(['callsign' => 'EGKK_TWR']);
        $ctsPosition2 = CtsPosition::factory()->create(['callsign' => 'EGKK_APP']);

        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => [$ctsPosition1->callsign, $ctsPosition2->callsign],
        ]);

        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        $trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        // Verify permissions were created for both positions
        $this->assertDatabaseHas('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition1->id,
        ], 'cts');

        $this->assertDatabaseHas('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition2->id,
        ], 'cts');

        // Act: Delete the training place
        $trainingPlace->delete();

        // Assert: Mentoring permissions should be revoked for both positions
        $this->assertDatabaseMissing('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition1->id,
        ], 'cts');

        $this->assertDatabaseMissing('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition2->id,
        ], 'cts');
    }

    #[Test]
    public function it_handles_deletion_when_student_has_no_cts_member(): void
    {
        // Arrange: Create data without a CTS member
        $ctsPosition = CtsPosition::factory()->create();
        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => [$ctsPosition->callsign],
        ]);

        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();

        // Do NOT create a CTS member for the student

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        $trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        // Act: Delete the training place (should not throw an exception)
        $trainingPlace->delete();

        // Assert: No position validations exist (there were none to begin with)
        $this->assertDatabaseMissing('position_validations', [
            'position_id' => $ctsPosition->id,
        ], 'cts');
    }

    #[Test]
    public function it_only_revokes_permissions_for_the_specific_student_and_positions(): void
    {
        // Arrange: Create two students with different training places
        $ctsPosition1 = CtsPosition::factory()->create(['callsign' => 'EGKK_TWR']);
        $ctsPosition2 = CtsPosition::factory()->create(['callsign' => 'EGKK_APP']);

        $trainingPosition1 = TrainingPosition::factory()->create([
            'cts_positions' => [$ctsPosition1->callsign],
        ]);

        $trainingPosition2 = TrainingPosition::factory()->create([
            'cts_positions' => [$ctsPosition2->callsign],
        ]);

        $waitingList = WaitingList::factory()->create();

        // Student 1
        $student1 = Account::factory()->create();
        Member::factory()->create(['cid' => $student1->id]);
        $waitingListAccount1 = $waitingList->addToWaitingList($student1, $this->privacc);
        $trainingPlace1 = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount1->id,
            'training_position_id' => $trainingPosition1->id,
        ]);

        // Student 2
        $student2 = Account::factory()->create();
        Member::factory()->create(['cid' => $student2->id]);
        $waitingListAccount2 = $waitingList->addToWaitingList($student2, $this->privacc);
        $trainingPlace2 = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount2->id,
            'training_position_id' => $trainingPosition2->id,
        ]);

        // Verify both students have permissions
        $this->assertDatabaseHas('position_validations', [
            'member_id' => $student1->member->id,
            'position_id' => $ctsPosition1->id,
        ], 'cts');

        $this->assertDatabaseHas('position_validations', [
            'member_id' => $student2->member->id,
            'position_id' => $ctsPosition2->id,
        ], 'cts');

        // Act: Delete only student1's training place
        $trainingPlace1->delete();

        // Assert: Only student1's permissions should be revoked
        $this->assertDatabaseMissing('position_validations', [
            'member_id' => $student1->member->id,
            'position_id' => $ctsPosition1->id,
        ], 'cts');

        // Student2's permissions should still exist
        $this->assertDatabaseHas('position_validations', [
            'member_id' => $student2->member->id,
            'position_id' => $ctsPosition2->id,
        ], 'cts');
    }
}
