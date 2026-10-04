<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Training;

use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\AvailabilityLogEntry;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Services\Training\AvailabilityLogService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AvailabilityLogServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected AvailabilityLogService $service;

    protected Account $account;

    protected Member $member;

    protected TrainingPlace $place;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::create(2026, 5, 10, 12, 0, 0));

        $this->service = new AvailabilityLogService;
        $this->account = Account::factory()->create();
        $this->member = Member::factory()->forAccount($this->account)->create();
        $this->place = TrainingPlace::factory()->createQuietly([
            'account_id' => $this->account->id,
        ]);
    }

    #[Test]
    public function it_returns_only_active_training_places_for_the_account(): void
    {
        TrainingPlace::factory()->createQuietly([
            'account_id' => $this->account->id,
        ]);

        $trashed = TrainingPlace::factory()->createQuietly([
            'account_id' => $this->account->id,
        ]);
        $trashed->delete();

        $places = $this->service->activeTrainingPlacesForAccount($this->account->id);

        $this->assertCount(2, $places);
        $this->assertTrue($places->contains('id', $this->place->id));
        $this->assertFalse($places->contains('id', $trashed->id));
    }

    #[Test]
    public function record_added_inserts_an_added_version_per_place(): void
    {
        $places = $this->service->activeTrainingPlacesForAccount($this->account->id);

        $this->service->recordAdded($places, Carbon::parse('2026-05-15 18:00:00'), Carbon::parse('2026-05-15 21:00:00'));

        $entry = AvailabilityLogEntry::where('training_place_id', $this->place->id)->sole();

        $this->assertSame('added', $entry->event->value);
        $this->assertSame('2026-05-15 18:00:00', $entry->slot_from->format('Y-m-d H:i:s'));
        $this->assertSame('2026-05-15 21:00:00', $entry->slot_to->format('Y-m-d H:i:s'));
        $this->assertSame('2026-05-10 12:00:00', $entry->created_at->format('Y-m-d H:i:s'));
        $this->assertNull($entry->superseded_at);
    }

    #[Test]
    public function record_removed_supersedes_the_current_version_without_a_successor(): void
    {
        $places = $this->service->activeTrainingPlacesForAccount($this->account->id);
        $current = AvailabilityLogEntry::factory()->create([
            'training_place_id' => $this->place->id,
            'event' => 'added',
            'slot_from' => '2026-05-15 18:00:00',
            'slot_to' => '2026-05-15 21:00:00',
            'created_at' => '2026-05-08 09:00:00',
            'superseded_at' => null,
        ]);

        $this->service->recordRemoved($places, Carbon::parse('2026-05-15 18:00:00'), Carbon::parse('2026-05-15 21:00:00'));

        $this->assertSame('2026-05-10 12:00:00', $current->fresh()->superseded_at->format('Y-m-d H:i:s'));
        $this->assertSame(1, AvailabilityLogEntry::where('training_place_id', $this->place->id)->count());
    }

    #[Test]
    public function record_merged_supersedes_the_old_version_and_inserts_a_merged_version_with_a_shared_timestamp(): void
    {
        $places = $this->service->activeTrainingPlacesForAccount($this->account->id);
        $old = AvailabilityLogEntry::factory()->create([
            'training_place_id' => $this->place->id,
            'event' => 'added',
            'slot_from' => '2026-05-15 18:00:00',
            'slot_to' => '2026-05-15 20:00:00',
            'created_at' => '2026-05-08 09:00:00',
            'superseded_at' => null,
        ]);

        $this->service->recordMerged(
            $places,
            Carbon::parse('2026-05-15 18:00:00'),
            Carbon::parse('2026-05-15 20:00:00'),
            Carbon::parse('2026-05-15 18:00:00'),
            Carbon::parse('2026-05-15 21:00:00'),
        );

        $merged = AvailabilityLogEntry::where('training_place_id', $this->place->id)
            ->where('event', 'merged')
            ->sole();

        $this->assertSame('2026-05-10 12:00:00', $old->fresh()->superseded_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-05-10 12:00:00', $merged->created_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-05-15 18:00:00', $merged->slot_from->format('Y-m-d H:i:s'));
        $this->assertSame('2026-05-15 21:00:00', $merged->slot_to->format('Y-m-d H:i:s'));
        $this->assertNull($merged->superseded_at);
        $this->assertSame(
            $old->fresh()->superseded_at->format('Y-m-d H:i:s'),
            $merged->created_at->format('Y-m-d H:i:s'),
            'Successor created_at must equal predecessor superseded_at (shared timestamp).',
        );
    }

    #[Test]
    public function record_edited_supersedes_and_inserts_an_edited_version(): void
    {
        $places = $this->service->activeTrainingPlacesForAccount($this->account->id);
        AvailabilityLogEntry::factory()->create([
            'training_place_id' => $this->place->id,
            'event' => 'added',
            'slot_from' => '2026-05-15 18:00:00',
            'slot_to' => '2026-05-15 21:00:00',
            'created_at' => '2026-05-08 09:00:00',
            'superseded_at' => null,
        ]);

        $this->service->recordEdited(
            $places,
            Carbon::parse('2026-05-15 18:00:00'),
            Carbon::parse('2026-05-15 21:00:00'),
            Carbon::parse('2026-05-16 17:00:00'),
            Carbon::parse('2026-05-16 20:00:00'),
        );

        $edited = AvailabilityLogEntry::where('training_place_id', $this->place->id)
            ->where('event', 'edited')
            ->sole();

        $this->assertSame('2026-05-16 17:00:00', $edited->slot_from->format('Y-m-d H:i:s'));
        $this->assertSame('2026-05-16 20:00:00', $edited->slot_to->format('Y-m-d H:i:s'));
        $this->assertSame(2, AvailabilityLogEntry::where('training_place_id', $this->place->id)->count());
    }

    #[Test]
    public function record_merged_without_a_matching_current_version_still_inserts(): void
    {
        $places = $this->service->activeTrainingPlacesForAccount($this->account->id);

        $this->service->recordMerged(
            $places,
            Carbon::parse('2026-05-15 18:00:00'),
            Carbon::parse('2026-05-15 20:00:00'),
            Carbon::parse('2026-05-15 18:00:00'),
            Carbon::parse('2026-05-15 21:00:00'),
        );

        $this->assertSame(1, AvailabilityLogEntry::where('training_place_id', $this->place->id)->count());
        $this->assertSame('merged', AvailabilityLogEntry::where('training_place_id', $this->place->id)->sole()->event->value);
    }

    #[Test]
    public function record_removed_without_a_matching_current_version_is_a_no_op(): void
    {
        $places = $this->service->activeTrainingPlacesForAccount($this->account->id);

        $this->service->recordRemoved($places, Carbon::parse('2026-05-15 18:00:00'), Carbon::parse('2026-05-15 21:00:00'));

        $this->assertSame(0, AvailabilityLogEntry::where('training_place_id', $this->place->id)->count());
    }

    #[Test]
    public function a_log_write_failure_never_throws(): void
    {
        Log::shouldReceive('warning')->once();

        $ghost = TrainingPlace::factory()->make();
        $ghost->id = str()->ulid(); // id that does not exist in DB -> FK violation on insert

        $this->service->recordAdded(collect([$ghost]), Carbon::parse('2026-05-15 18:00:00'), Carbon::parse('2026-05-15 21:00:00'));

        $this->assertSame(0, AvailabilityLogEntry::where('training_place_id', $ghost->id)->count());
    }
}
