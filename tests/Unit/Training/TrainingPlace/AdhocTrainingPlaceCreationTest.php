<?php

namespace Tests\Unit\Training\TrainingPlace;

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
        $trainingPosition = TrainingPosition::factory()->create();

        $trainingPlace = $this->service->createAdhocTrainingPlace($student, $trainingPosition);

        $this->assertInstanceOf(TrainingPlace::class, $trainingPlace);
        $this->assertNull($trainingPlace->waiting_list_account_id);
        $this->assertEquals($student->id, $trainingPlace->account_id);
        $this->assertEquals($trainingPosition->id, $trainingPlace->training_position_id);
        $this->assertTrue($trainingPlace->studentAccount()->is($student));
    }
}
