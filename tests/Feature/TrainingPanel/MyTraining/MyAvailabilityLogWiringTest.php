<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\MyTraining;

use App\Filament\Training\Pages\MyTraining\MyAvailability;
use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\AvailabilityLogEntry;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class MyAvailabilityLogWiringTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected Account $studentAccount;

    protected Member $studentMember;

    protected TrainingPlace $place;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::create(2026, 5, 10, 12, 0, 0));

        $this->studentAccount = Account::factory()->create();
        $this->studentMember = Member::factory()->forAccount($this->studentAccount)->create();
        $this->studentAccount->givePermissionTo('training.access');

        $this->place = TrainingPlace::factory()->createQuietly([
            'account_id' => $this->studentAccount->id,
        ]);
    }

    #[Test]
    public function adding_availability_writes_an_added_version(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.date_range', ['start' => $tomorrow, 'end' => $tomorrow])
            ->set('data.from', '18:00')
            ->set('data.to', '21:00')
            ->call('create');

        $entry = AvailabilityLogEntry::where('training_place_id', $this->place->id)->sole();

        $this->assertSame('added', $entry->event->value);
        $this->assertSame("{$tomorrow} 18:00:00", $entry->slot_from->format('Y-m-d H:i:s'));
        $this->assertSame("{$tomorrow} 21:00:00", $entry->slot_to->format('Y-m-d H:i:s'));
        $this->assertNull($entry->superseded_at);
    }

    #[Test]
    public function adding_availability_logs_to_every_active_training_place(): void
    {
        $secondPlace = TrainingPlace::factory()->createQuietly([
            'account_id' => $this->studentAccount->id,
        ]);

        $tomorrow = now()->addDay()->toDateString();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.date_range', ['start' => $tomorrow, 'end' => $tomorrow])
            ->set('data.from', '18:00')
            ->set('data.to', '21:00')
            ->call('create');

        $this->assertSame(2, AvailabilityLogEntry::where('slot_from', "{$tomorrow} 18:00:00")->count());
        $this->assertSame(1, AvailabilityLogEntry::where('training_place_id', $secondPlace->id)->count());
    }

    #[Test]
    public function merging_availability_writes_a_merged_version_and_supersedes_the_current_one(): void
    {
        $date = now()->addDay()->toDateString();

        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $date,
            'from' => '18:00:00',
            'to' => '20:00:00',
            'type' => 'S',
        ]);

        $added = AvailabilityLogEntry::factory()->create([
            'training_place_id' => $this->place->id,
            'event' => 'added',
            'slot_from' => "{$date} 18:00:00",
            'slot_to' => "{$date} 20:00:00",
            'created_at' => now()->subDay(),
            'superseded_at' => null,
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.date_range', ['start' => $date, 'end' => $date])
            ->set('data.from', '19:00')
            ->set('data.to', '21:00')
            ->call('create');

        $merged = AvailabilityLogEntry::where('training_place_id', $this->place->id)
            ->where('event', 'merged')
            ->sole();

        $this->assertNotNull($added->fresh()->superseded_at);
        $this->assertSame("{$date} 18:00:00", $merged->slot_from->format('Y-m-d H:i:s'));
        $this->assertSame("{$date} 21:00:00", $merged->slot_to->format('Y-m-d H:i:s'));
        $this->assertSame(
            $added->fresh()->superseded_at->format('Y-m-d H:i:s'),
            $merged->created_at->format('Y-m-d H:i:s'),
            'Shared timestamp invariant broken.',
        );
    }

    #[Test]
    public function editing_availability_writes_an_edited_version(): void
    {
        $date = now()->addDay()->toDateString();

        $slot = Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $date,
            'from' => '18:00:00',
            'to' => '21:00:00',
            'type' => 'S',
        ]);

        $added = AvailabilityLogEntry::factory()->create([
            'training_place_id' => $this->place->id,
            'event' => 'added',
            'slot_from' => "{$date} 18:00:00",
            'slot_to' => "{$date} 21:00:00",
            'created_at' => now()->subDay(),
            'superseded_at' => null,
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->callTableAction('edit', $slot, [
                'date' => $date,
                'from' => '17:00',
                'to' => '20:00',
            ]);

        $edited = AvailabilityLogEntry::where('training_place_id', $this->place->id)
            ->where('event', 'edited')
            ->sole();

        $this->assertNotNull($added->fresh()->superseded_at, 'Editing must supersede the previous version.');
        $this->assertSame("{$date} 17:00:00", $edited->slot_from->format('Y-m-d H:i:s'));
        $this->assertSame("{$date} 20:00:00", $edited->slot_to->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function deleting_availability_supersedes_the_current_version_without_a_removed_event(): void
    {
        $date = now()->addDay()->toDateString();

        $slot = Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $date,
            'from' => '18:00:00',
            'to' => '21:00:00',
            'type' => 'S',
        ]);

        $current = AvailabilityLogEntry::factory()->create([
            'training_place_id' => $this->place->id,
            'event' => 'added',
            'slot_from' => "{$date} 18:00:00",
            'slot_to' => "{$date} 21:00:00",
            'created_at' => now()->subDay(),
            'superseded_at' => null,
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->callTableAction('delete', $slot);

        $this->assertNotNull($current->fresh()->superseded_at);
        $this->assertSame(1, AvailabilityLogEntry::where('training_place_id', $this->place->id)->count());
    }

    #[Test]
    public function bulk_deleting_supersedes_each_current_version_without_removed_events(): void
    {
        $date = now()->addDay()->toDateString();

        $slotA = Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $date,
            'from' => '18:00:00',
            'to' => '19:00:00',
            'type' => 'S',
        ]);

        $slotB = Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $date,
            'from' => '20:00:00',
            'to' => '21:00:00',
            'type' => 'S',
        ]);

        $entryA = AvailabilityLogEntry::factory()->create([
            'training_place_id' => $this->place->id,
            'event' => 'added',
            'slot_from' => "{$date} 18:00:00",
            'slot_to' => "{$date} 19:00:00",
            'created_at' => now()->subDay(),
            'superseded_at' => null,
        ]);

        $entryB = AvailabilityLogEntry::factory()->create([
            'training_place_id' => $this->place->id,
            'event' => 'added',
            'slot_from' => "{$date} 20:00:00",
            'slot_to' => "{$date} 21:00:00",
            'created_at' => now()->subDay(),
            'superseded_at' => null,
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->callTableBulkAction('delete', [$slotA->id, $slotB->id]);

        $this->assertNotNull($entryA->fresh()->superseded_at);
        $this->assertNotNull($entryB->fresh()->superseded_at);
    }
}
