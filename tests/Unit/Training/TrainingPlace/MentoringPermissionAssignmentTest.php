<?php

namespace Tests\Unit\Training\TrainingPlace;

use App\Enums\PositionValidationStatusEnum;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Cts\PositionValidation;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Services\Training\TrainingPlaceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MentoringPermissionAssignmentTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // disable training place observer
        Event::fake();
    }

    #[Test]
    public function it_can_assign_mentoring_permissions_to_a_training_place()
    {
        $ctsPosition = CtsPosition::factory()->create();
        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => [$ctsPosition->callsign],
        ]);

        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();
        // create CTS member
        Member::factory()->create(['cid' => $student->id]);

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        $trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);
        $service = new TrainingPlaceService;
        $service->assignMentoringPermissions($trainingPlace);

        $this->assertDatabaseHas('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition->id,
            'status' => PositionValidationStatusEnum::Student->value,
        ], 'cts');
    }

    #[Test]
    public function it_will_not_assign_mentoring_permissions_if_the_student_does_not_have_a_cts_member_model_attached()
    {
        $ctsPosition = CtsPosition::factory()->create();
        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => [$ctsPosition->callsign],
        ]);

        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        $trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        $service = new TrainingPlaceService;
        $service->assignMentoringPermissions($trainingPlace);

        $this->assertDatabaseMissing('position_validations', [
            'position_id' => $ctsPosition->id,
        ], 'cts');
    }

    #[Test]
    public function it_will_remove_mentoring_permissions()
    {
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

        PositionValidation::create([
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition->id,
            'status' => PositionValidationStatusEnum::Student->value,
            'changed_by' => $student->id,
            'date_changed' => now(),
        ]);

        $service = new TrainingPlaceService;
        $service->revokeMentoringPermissions($trainingPlace);

        $this->assertDatabaseMissing('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition->id,
            'status' => PositionValidationStatusEnum::Student->value,
        ], 'cts');
    }

    #[Test]
    public function it_only_deletes_student_status_position_validations()
    {
        $ctsPositionForStudent = CtsPosition::factory()->create();
        $ctsPositionForMentor = CtsPosition::factory()->create();
        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => [$ctsPositionForStudent->callsign],
        ]);

        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        $trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        // Create a mentor validation for a different position that should NOT be deleted
        $mentorValidation = PositionValidation::create([
            'member_id' => $student->member->id,
            'position_id' => $ctsPositionForMentor->id,
            'status' => PositionValidationStatusEnum::Mentor->value,
            'changed_by' => $student->id,
            'date_changed' => now(),
        ]);

        // Create a student validation that SHOULD be deleted
        $studentValidation = PositionValidation::create([
            'member_id' => $student->member->id,
            'position_id' => $ctsPositionForStudent->id,
            'status' => PositionValidationStatusEnum::Student->value,
            'changed_by' => $student->id,
            'date_changed' => now(),
        ]);

        $service = new TrainingPlaceService;
        $service->revokeMentoringPermissions($trainingPlace);

        // Mentor validation should still exist
        $this->assertDatabaseHas('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPositionForMentor->id,
            'status' => PositionValidationStatusEnum::Mentor->value,
        ], 'cts');

        // Student validation should be deleted
        $this->assertDatabaseMissing('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPositionForStudent->id,
            'status' => PositionValidationStatusEnum::Student->value,
        ], 'cts');
    }

    #[Test]
    public function it_will_not_revoke_mentoring_permissions_if_the_student_does_not_have_a_cts_member_model_attached()
    {
        $ctsPosition = CtsPosition::factory()->create();
        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => [$ctsPosition->callsign],
        ]);

        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        $trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        $service = new TrainingPlaceService;
        // Should not throw an exception
        $service->revokeMentoringPermissions($trainingPlace);

        // Test passes if no exception is thrown
        $this->assertTrue(true);
    }

    #[Test]
    public function it_revokes_mentoring_permissions_for_multiple_cts_positions()
    {
        $ctsPosition1 = CtsPosition::factory()->create(['callsign' => 'EGGD_TWR']);
        $ctsPosition2 = CtsPosition::factory()->create(['callsign' => 'EGGD_APP']);
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

        // First assign permissions for both positions
        $service = new TrainingPlaceService;
        $service->assignMentoringPermissions($trainingPlace);

        // Verify both exist
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

        // Now revoke them
        $service->revokeMentoringPermissions($trainingPlace);

        // Verify both are deleted
        $this->assertDatabaseMissing('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition1->id,
            'status' => PositionValidationStatusEnum::Student->value,
        ], 'cts');

        $this->assertDatabaseMissing('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition2->id,
            'status' => PositionValidationStatusEnum::Student->value,
        ], 'cts');
    }
}
