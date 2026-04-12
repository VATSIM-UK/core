<?php

namespace Tests\Unit\Training\TrainingPlace;

use App\Models\Cts\Position as CtsPosition;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\TrainingPlaceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdhocTrainingPlaceCreationTest extends TestCase
{
    use DatabaseTransactions;

    private TrainingPlaceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TrainingPlaceService;
    }

    #[Test]
    public function it_can_create_a_training_place_without_a_waiting_list_account(): void
    {
        $student = Account::factory()->create();
        $ctsPosition = CtsPosition::factory()->create(['callsign' => 'EGLL_TWR']);
        $trainingPosition = TrainingPosition::factory()->withCtsPositions([$ctsPosition->callsign])->create();

        $reason = 'This is a valid reason for creating an ad-hoc training place.';
        $actor = $this->privacc;

        $trainingPlace = $this->service->createAdhocTrainingPlace($student, $trainingPosition, $reason, $actor);

        $this->assertInstanceOf(TrainingPlace::class, $trainingPlace);
        $this->assertNull($trainingPlace->waiting_list_account_id);
        $this->assertEquals($student->id, $trainingPlace->account_id);
        $this->assertEquals($trainingPosition->id, $trainingPlace->training_position_id);
        $this->assertTrue($trainingPlace->studentAccount()->is($student));

        $this->assertDatabaseHas('mship_account_note', [
            'account_id' => $student->id,
            'writer_id' => $actor->id,
            'content' => "Ad-hoc training place created on {$ctsPosition->callsign} outside the usual waiting list flow. Reason: {$reason}",
        ]);
    }
}
