<?php

namespace Tests\Unit\Training\TrainingPlace;

use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckAvailabilityForActiveTrainingPlacesTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_can_check_availability_for_active_training_places()
    {
        $trainingPlace = $this->createActiveTrainingPlace();

        $this->artisan('training-places:check-availability');

        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $trainingPlace->id,
        ]);

    }

    #[Test]
    public function it_does_not_check_availability_for_inactive_training_places()
    {
        $trainingPlace = TrainingPlace::factory()->create(['deleted_at' => now()]);

        $this->artisan('training-places:check-availability');

        $this->assertDatabaseMissing('availability_checks', [
            'training_place_id' => $trainingPlace->id,
        ]);
    }

    private function createActiveTrainingPlace(): TrainingPlace
    {
        $account = Account::factory()->create();
        // create cts member first as the cid is not overwritten when using a factory
        Member::factory()->create(['cid' => $account->id]);

        $waitingList = WaitingList::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($account, $account);
        $trainingPosition = TrainingPosition::factory()->create();

        return TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);
    }
}
