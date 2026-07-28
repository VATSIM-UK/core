<?php

namespace Tests\Feature\Training;

use App\Models\Mship\Qualification;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainableWaitingListMigrationTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_replaces_the_legacy_pivot_table_with_the_polymorphic_one()
    {
        $this->assertFalse(
            Schema::hasTable('training_position_waiting_list'),
            'The legacy training_position_waiting_list table should have been dropped.',
        );

        $this->assertTrue(Schema::hasTable('trainable_waiting_list'));
        $this->assertTrue(Schema::hasColumns('trainable_waiting_list', [
            'trainable_type',
            'trainable_id',
            'waiting_list_id',
            'created_at',
            'updated_at',
        ]));
    }

    #[Test]
    public function backfilled_position_rows_resolve_through_the_polymorphic_relations()
    {
        $waitingList = WaitingList::factory()->create();
        $trainingPosition = TrainingPosition::factory()->create();

        // Simulate a row produced by the migration backfill (raw insert, not the relation).
        DB::table('trainable_waiting_list')->insert([
            'trainable_type' => TrainingPosition::class,
            'trainable_id' => $trainingPosition->id,
            'waiting_list_id' => $waitingList->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertTrue($waitingList->trainingPositions()->whereKey($trainingPosition->id)->exists());
        $this->assertTrue($trainingPosition->waitingLists()->whereKey($waitingList->id)->exists());
        $this->assertCount(0, $waitingList->qualifications);
    }

    #[Test]
    public function the_unique_key_covers_the_full_morph_tuple()
    {
        $waitingList = WaitingList::factory()->create();
        $trainingPosition = TrainingPosition::factory()->create();
        $qualification = Qualification::factory()->pilot()->create();

        // Same id across different morph types must be allowed.
        $waitingList->trainingPositions()->attach($trainingPosition->id);
        $waitingList->qualifications()->attach($qualification->id);

        $this->assertDatabaseHas('trainable_waiting_list', [
            'trainable_type' => TrainingPosition::class,
            'trainable_id' => $trainingPosition->id,
            'waiting_list_id' => $waitingList->id,
        ]);
        $this->assertDatabaseHas('trainable_waiting_list', [
            'trainable_type' => Qualification::class,
            'trainable_id' => $qualification->id,
            'waiting_list_id' => $waitingList->id,
        ]);
    }
}
