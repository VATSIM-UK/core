<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\Training;

use App\Livewire\Training\TrainingPlaceExamCancellationsTable;
use App\Models\Atc\Position;
use App\Models\Cts\CancelReason;
use App\Models\Cts\ExamBooking;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class TrainingPlaceExamCancellationsTableTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected TrainingPlace $trainingPlace;

    protected string $callsign = 'EGKK_APP';

    protected function setUp(): void
    {
        parent::setUp();

        $this->trainingPlace = TrainingPlace::factory()->for(TrainingPosition::factory()->for(Position::factory(['callsign' => $this->callsign]), 'position'), 'trainingPosition')->create();
        $this->trainingPlace->trainingPosition->position->update(['callsign' => $this->callsign]);
    }

    #[Test]
    public function it_renders_successfully(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(TrainingPlaceExamCancellationsTable::class, ['trainingPlace' => $this->trainingPlace])
            ->assertStatus(200);
    }

    #[Test]
    public function it_lists_exam_cancellations_matching_the_training_place_position(): void
    {
        $matchingBooking = ExamBooking::factory()->create(['position_1' => $this->callsign]);
        $matchingCancellation = CancelReason::factory()->create([
            'sesh_id' => $matchingBooking->id,
            'sesh_type' => 'EX',
            'reason' => 'Matching Exam Cancellation',
        ]);

        $wrongPositionBooking = ExamBooking::factory()->create(['position_1' => 'EGCC_TWR']);
        $wrongPositionCancellation = CancelReason::factory()->create([
            'sesh_id' => $wrongPositionBooking->id,
            'sesh_type' => 'EX',
            'reason' => 'Wrong Position Cancellation',
        ]);

        $mentoringBooking = ExamBooking::factory()->create(['position_1' => $this->callsign]);
        $mentoringCancellation = CancelReason::factory()->create([
            'sesh_id' => $mentoringBooking->id,
            'sesh_type' => 'ME',
            'reason' => 'Mentoring Cancellation',
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(TrainingPlaceExamCancellationsTable::class, ['trainingPlace' => $this->trainingPlace])
            ->assertCanSeeTableRecords([$matchingCancellation])
            ->assertCanNotSeeTableRecords([$wrongPositionCancellation, $mentoringCancellation]);
    }

    #[Test]
    public function it_shows_empty_state_when_no_cancellations_exist(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(TrainingPlaceExamCancellationsTable::class, ['trainingPlace' => $this->trainingPlace])
            ->assertSee('No exam cancellations found for this training place.');
    }
}
