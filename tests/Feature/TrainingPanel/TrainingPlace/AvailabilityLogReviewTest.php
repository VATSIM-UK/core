<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\TrainingPlace;

use App\Livewire\Training\AvailabilityLogReview;
use App\Livewire\Training\AvailabilityLogTable;
use App\Models\Training\TrainingPlace\AvailabilityLogEntry;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class AvailabilityLogReviewTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected TrainingPlace $place;

    protected function setUp(): void
    {
        parent::setUp();

        $this->panelUser->givePermissionTo('training-places.view.*');

        $this->place = TrainingPlace::factory()->createQuietly();
    }

    private function seedChain(): void
    {
        $added = AvailabilityLogEntry::factory()->create([
            'training_place_id' => $this->place->id,
            'event' => 'added',
            'slot_from' => '2026-05-15 18:00:00',
            'slot_to' => '2026-05-15 21:00:00',
            'created_at' => '2026-05-14 09:00:00',
            'superseded_at' => null,
        ]);

        $edited = AvailabilityLogEntry::factory()->create([
            'training_place_id' => $this->place->id,
            'event' => 'edited',
            'slot_from' => '2026-05-15 18:00:00',
            'slot_to' => '2026-05-15 22:00:00',
            'created_at' => '2026-05-16 10:00:00',
            'superseded_at' => null,
        ]);

        $added->update(['superseded_at' => '2026-05-16 10:00:00']);
        $edited->update(['superseded_at' => '2026-05-18 12:00:00']);
    }

    #[Test]
    public function snapshot_is_empty_when_no_entries_exist_before_the_as_of_time(): void
    {
        $component = Livewire::actingAs($this->panelUser)
            ->test(AvailabilityLogReview::class, ['trainingPlace' => $this->place])
            ->set('data.asOf', '2026-05-13 12:00');

        $this->assertCount(0, $component->instance()->getTable()->getRecords());
    }

    #[Test]
    public function snapshot_reconstructs_the_active_slot_at_each_point_in_time(): void
    {
        $this->seedChain();

        $component = Livewire::actingAs($this->panelUser)
            ->test(AvailabilityLogReview::class, ['trainingPlace' => $this->place]);

        $component->set('data.asOf', '2026-05-15 12:00');
        $records = $component->instance()->getTable()->getRecords();
        $this->assertCount(1, $records);
        $this->assertSame('2026-05-15 21:00:00', $records->first()->slot_to->format('Y-m-d H:i:s'));

        $component->set('data.asOf', '2026-05-17 12:00');
        $records = $component->instance()->getTable()->getRecords();
        $this->assertCount(1, $records);
        $this->assertSame('2026-05-15 22:00:00', $records->first()->slot_to->format('Y-m-d H:i:s'));

        $component->set('data.asOf', '2026-05-19 12:00');
        $this->assertCount(0, $component->instance()->getTable()->getRecords());
    }

    #[Test]
    public function snapshot_at_the_exact_shared_timestamp_shows_only_the_successor(): void
    {
        $this->seedChain();

        $component = Livewire::actingAs($this->panelUser)
            ->test(AvailabilityLogReview::class, ['trainingPlace' => $this->place])
            ->set('data.asOf', '2026-05-16 10:00');

        $records = $component->instance()->getTable()->getRecords();

        $this->assertCount(1, $records, 'At the shared timestamp exactly one version must be active (no double count).');
        $this->assertSame('edited', $records->first()->event->value);
    }

    #[Test]
    public function it_renders_successfully_with_an_empty_log(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(AvailabilityLogReview::class, ['trainingPlace' => $this->place])
            ->assertSuccessful();
    }

    #[Test]
    public function narrative_table_marks_the_removed_version(): void
    {
        $this->seedChain();

        $component = Livewire::actingAs($this->panelUser)
            ->test(AvailabilityLogTable::class, ['trainingPlace' => $this->place]);

        $statuses = collect($component->instance()->getTable()->getRecords())
            ->mapWithKeys(fn (AvailabilityLogEntry $entry) => [$entry->event->value => $entry]);

        $this->assertSame('Changed', $component->instance()->logEntryStatus($statuses['added']));
        $this->assertSame('Removed', $component->instance()->logEntryStatus($statuses['edited']));
    }

    #[Test]
    public function narrative_table_lists_all_versions_newest_first(): void
    {
        $this->seedChain();

        $component = Livewire::actingAs($this->panelUser)
            ->test(AvailabilityLogTable::class, ['trainingPlace' => $this->place]);

        $events = collect($component->instance()->getTable()->getRecords())
            ->pluck('event.value');

        $this->assertSame(['edited', 'added'], $events->all());
    }
}
