<?php

namespace Tests\Unit\Training\TrainingPlace;

use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Services\Training\TrainingPlaceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ManualTrainingPlaceCreationTest extends TestCase
{
    use DatabaseTransactions;

    private TrainingPlaceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TrainingPlaceService;
    }

    #[Test]
    public function it_creates_a_training_place_manually()
    {
        // Arrange: Create the necessary data
        $this->actingAs($this->privacc);

        $ctsPosition = \App\Models\Cts\Position::factory()->create(['callsign' => 'EGLL_TWR']);
        $trainingPosition = TrainingPosition::factory()->withCtsPositions([$ctsPosition->callsign])->create();
        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        // Act: Create a manual training place
        $trainingPlace = $this->service->createManualTrainingPlace($waitingListAccount, $trainingPosition);

        // Assert: The training place should be created
        $this->assertInstanceOf(TrainingPlace::class, $trainingPlace);
        $this->assertEquals($waitingListAccount->id, $trainingPlace->waiting_list_account_id);
        $this->assertEquals($trainingPosition->id, $trainingPlace->training_position_id);
        $this->assertDatabaseHas('training_places', [
            'id' => $trainingPlace->id,
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);
    }

    #[Test]
    public function it_removes_user_from_waiting_list_when_creating_manual_training_place()
    {
        // Arrange
        $this->actingAs($this->privacc);

        $ctsPosition = \App\Models\Cts\Position::factory()->create(['callsign' => 'EGLL_APP']);
        $trainingPosition = TrainingPosition::factory()->withCtsPositions([$ctsPosition->callsign])->create();
        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        // Assert the user is on the waiting list
        $this->assertNull($waitingListAccount->fresh()->deleted_at);

        // Act: Create a manual training place (should remove from waiting list)
        $trainingPlace = $this->service->createManualTrainingPlace($waitingListAccount->fresh(), $trainingPosition);

        // Assert: The user should be soft deleted (removed from waiting list)
        $this->assertNotNull($waitingListAccount->fresh()->deleted_at);
        $this->assertNotNull($waitingListAccount->fresh()->removal_type);
    }

    #[Test]
    public function it_returns_the_created_training_place()
    {
        // Arrange
        $this->actingAs($this->privacc);

        $ctsPosition = \App\Models\Cts\Position::factory()->create(['callsign' => 'EGKK_TWR']);
        $trainingPosition = TrainingPosition::factory()->withCtsPositions([$ctsPosition->callsign])->create();
        $waitingList = WaitingList::factory()->create();
        $student = Account::factory()->create();
        Member::factory()->create(['cid' => $student->id]);

        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        // Act
        $result = $this->service->createManualTrainingPlace($waitingListAccount, $trainingPosition);

        // Assert
        $this->assertInstanceOf(TrainingPlace::class, $result);
        $this->assertTrue($result->exists);
        $this->assertEquals($waitingListAccount->id, $result->waiting_list_account_id);
    }
}
