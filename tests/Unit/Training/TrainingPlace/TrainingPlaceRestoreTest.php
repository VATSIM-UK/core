<?php

namespace Tests\Unit\Training\TrainingPlace;

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

class TrainingPlaceRestoreTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_reassigns_mentoring_permissions_when_restored_after_soft_delete(): void
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

        $this->assertDatabaseHas('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition->id,
            'status' => PositionValidationStatusEnum::Student->value,
        ], 'cts');

        $trainingPlace->delete();

        $this->assertDatabaseMissing('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition->id,
            'status' => PositionValidationStatusEnum::Student->value,
        ], 'cts');

        $trainingPlace->restore();

        $this->assertDatabaseHas('position_validations', [
            'member_id' => $student->member->id,
            'position_id' => $ctsPosition->id,
            'status' => PositionValidationStatusEnum::Student->value,
        ], 'cts');
    }
}
