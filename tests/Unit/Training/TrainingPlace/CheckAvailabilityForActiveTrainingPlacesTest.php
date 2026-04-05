<?php

declare(strict_types=1);

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
        $trainingPlace->forceFill([
            'created_at' => now()->subHours(TrainingPlace::AVAILABILITY_CHECK_GRACE_PERIOD_HOURS + 1),
        ])->saveQuietly();

        $this->artisan('training-places:check-availability');

        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $trainingPlace->id,
        ]);

    }

    #[Test]
    public function it_does_not_create_availability_checks_during_grace_period_after_creation(): void
    {
        $trainingPlace = $this->createActiveTrainingPlace();

        $this->assertTrue(
            $trainingPlace->fresh()->isWithinAvailabilityCheckGracePeriod(),
            'Fixture must be within grace; created_at='.$trainingPlace->fresh()->created_at->toIso8601String().', now='.now()->toIso8601String()
        );

        $this->artisan('training-places:check-availability');

        $this->assertDatabaseMissing('availability_checks', [
            'training_place_id' => $trainingPlace->id,
        ]);
    }

    #[Test]
    public function it_does_not_check_availability_for_inactive_training_places()
    {
        $trainingPlace = $this->createActiveTrainingPlace();
        $trainingPlace->delete();

        $this->artisan('training-places:check-availability');

        $this->assertDatabaseMissing('availability_checks', [
            'training_place_id' => $trainingPlace->id,
        ]);
    }

    private function createActiveTrainingPlace(): TrainingPlace
    {
        // Create CTS member first so account id can match member cid (required for $account->member in CheckAvailability)
        $ctsMember = Member::factory()->create();
        $account = Account::factory()->create(['id' => $ctsMember->cid]);

        $waitingList = WaitingList::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($account, Account::factory()->create());
        $trainingPosition = TrainingPosition::factory()->create(['cts_positions' => []]);

        return TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);
    }
}
