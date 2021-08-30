<?php

namespace Tests\Unit\Training\TrainingPlace;

use App\Models\Station;
use App\Models\Training\TrainingPlace;
use App\Models\Training\TrainingPlace\TrainingPosition;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TrainingPositionTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->waitingList = factory(WaitingList::class)->create();
    }

    /** @test */
    public function itShouldGenerateArrayOfavailablePlacesForWaitingList()
    {
        $station = factory(Station::class)->create();
        $trainingPosition = TrainingPosition::factory()->create([
            'places' => 3,
            'station_id' => $station->id,
            'waiting_list_id' => $this->waitingList->id,
        ]);

        $this->assertEquals([
            'callsign' => $station->callsign,
            'id' => $trainingPosition->id,
        ], TrainingPosition::availablePlacesForWaitingList($this->waitingList)[0]);
    }

    /** @test */
    public function itShouldNotShowAvailablePlacesWhenAllFull()
    {
        $trainingPosition = TrainingPosition::factory()->create([
            'places' => 1,
            'waiting_list_id' => $this->waitingList->id,
        ]);

        // create an active training place on the training position registered.
        TrainingPlace::factory()->create([
            'training_position_id' => $trainingPosition->id,
        ]);

        $this->assertEquals([], TrainingPosition::availablePlacesForWaitingList($this->waitingList));
    }

    /** @test */
    public function itShouldHandlePlacesAcrossMultipleTrainingPositions()
    {
        [$firstPosition, $secondPosition] = TrainingPosition::factory()->count(2)->create([
            'places' => 1,
            'waiting_list_id' => $this->waitingList->id,
        ]);

        $this->assertEquals([
            ['id' => $firstPosition->id, 'callsign' => $firstPosition->station->callsign],
            ['id' => $secondPosition->id, 'callsign' => $secondPosition->station->callsign],
        ], TrainingPosition::availablePlacesForWaitingList($this->waitingList));
    }
}
