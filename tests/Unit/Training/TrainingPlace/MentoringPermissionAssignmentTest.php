<?php

namespace Tests\Unit\Training\TrainingPlace;

use App\Enums\PositionValidationStatusEnum;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
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
            'cts_positions' => [$ctsPosition->id],
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
            'status' => PositionValidationStatusEnum::Mentor->value,
        ], 'cts');
    }

    #[Test]
    public function it_will_not_assign_mentoring_permissions_if_the_student_does_not_have_a_cts_member_model_attached()
    {
        $ctsPosition = CtsPosition::factory()->create();
        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => [$ctsPosition->id],
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
}
