<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\MyTraining;

use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\Availability;
use App\Models\Training\TrainingPlace\AvailabilityLogEntry;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Services\Training\Availability\AvailabilityChange;
use App\Services\Training\Availability\AvailabilityChangeType;
use App\Services\Training\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class AvailabilityServiceApplyTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected Account $studentAccount;

    protected Member $studentMember;

    protected TrainingPlace $place;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::create(2026, 5, 13, 12, 0, 0));

        $this->studentAccount = Account::factory()->create();
        $this->studentMember = Member::factory()->forAccount($this->studentAccount)->create();
        $this->studentAccount->givePermissionTo('training.access');

        $this->place = TrainingPlace::factory()->createQuietly([
            'account_id' => $this->studentAccount->id,
        ]);
    }

    #[Test]
    public function it_creates_a_row_and_logs_added(): void
    {
        $change = new AvailabilityChange(AvailabilityChangeType::Added, null, null, null, Carbon::parse('2026-05-11 18:00', 'UTC'), Carbon::parse('2026-05-11 21:00', 'UTC'));

        $result = app(AvailabilityService::class)->applyChanges($this->studentMember->id, [$change]);

        $this->assertSame(1, $result['added']);
        $this->assertFalse($result['refused']);
        $this->assertSame(0, $result['edited']);
        $this->assertSame(0, $result['merged']);
        $this->assertSame(0, $result['removed']);
        $this->assertSame(1, Availability::where('student_id', $this->studentMember->id)->count());

        $entry = AvailabilityLogEntry::where('training_place_id', $this->place->id)->sole();
        $this->assertSame('added', $entry->event->value);
        $this->assertSame('2026-05-11 18:00:00', $entry->slot_from->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function it_updates_a_row_and_logs_edited_superseding_the_current_entry(): void
    {
        $row = Availability::factory()->forStudent($this->studentMember->id)->create(['date' => '2026-05-11', 'from' => '18:00:00', 'to' => '21:00:00', 'type' => 'S']);
        $current = AvailabilityLogEntry::factory()->create(['training_place_id' => $this->place->id, 'event' => 'added', 'slot_from' => '2026-05-11 18:00:00', 'slot_to' => '2026-05-11 21:00:00', 'superseded_at' => null]);

        $change = new AvailabilityChange(AvailabilityChangeType::Edited, $row, Carbon::parse('2026-05-11 18:00', 'UTC'), Carbon::parse('2026-05-11 21:00', 'UTC'), Carbon::parse('2026-05-11 18:30', 'UTC'), Carbon::parse('2026-05-11 21:00', 'UTC'));

        $result = app(AvailabilityService::class)->applyChanges($this->studentMember->id, [$change]);

        $this->assertSame(1, $result['edited']);
        $this->assertSame('18:30:00', $row->fresh()->getRawOriginal('from'));
        $this->assertNotNull($current->fresh()->superseded_at);
        $this->assertSame('edited', AvailabilityLogEntry::where('training_place_id', $this->place->id)->where('event', 'edited')->sole()->event->value);
    }

    #[Test]
    public function it_merges_rows_logging_merged_on_the_survivor_and_removed_for_the_swallowed_row(): void
    {
        $row = Availability::factory()->forStudent($this->studentMember->id)->create(['date' => '2026-05-11', 'from' => '18:00:00', 'to' => '19:00:00', 'type' => 'S']);
        $later = Availability::factory()->forStudent($this->studentMember->id)->create(['date' => '2026-05-11', 'from' => '20:00:00', 'to' => '21:00:00', 'type' => 'S']);

        $current = AvailabilityLogEntry::factory()->create(['training_place_id' => $this->place->id, 'event' => 'added', 'slot_from' => '2026-05-11 18:00:00', 'slot_to' => '2026-05-11 19:00:00', 'superseded_at' => null]);
        $swallowedCurrent = AvailabilityLogEntry::factory()->create(['training_place_id' => $this->place->id, 'event' => 'added', 'slot_from' => '2026-05-11 20:00:00', 'slot_to' => '2026-05-11 21:00:00', 'superseded_at' => null]);

        $change = new AvailabilityChange(
            AvailabilityChangeType::Merged,
            $row,
            Carbon::parse('2026-05-11 18:00', 'UTC'),
            Carbon::parse('2026-05-11 19:00', 'UTC'),
            Carbon::parse('2026-05-11 18:00', 'UTC'),
            Carbon::parse('2026-05-11 21:00', 'UTC'),
            [$later],
        );

        $result = app(AvailabilityService::class)->applyChanges($this->studentMember->id, [$change]);

        $this->assertSame(1, $result['merged']);
        $this->assertSame(1, $result['removed']);
        $this->assertFalse($result['refused']);
        $this->assertSame(1, Availability::where('student_id', $this->studentMember->id)->count());

        $survivor = Availability::where('student_id', $this->studentMember->id)->sole();
        $this->assertSame('2026-05-11', $survivor->getRawOriginal('date'));
        $this->assertSame('18:00:00', $survivor->getRawOriginal('from'));
        $this->assertSame('21:00:00', $survivor->getRawOriginal('to'));

        $this->assertNotNull($current->fresh()->superseded_at);
        $this->assertNotNull($swallowedCurrent->fresh()->superseded_at);
        $this->assertSame('merged', AvailabilityLogEntry::where('training_place_id', $this->place->id)->where('event', 'merged')->sole()->event->value);
    }

    #[Test]
    public function it_deletes_a_row_and_logs_removed(): void
    {
        $row = Availability::factory()->forStudent($this->studentMember->id)->create(['date' => '2026-05-11', 'from' => '18:00:00', 'to' => '21:00:00', 'type' => 'S']);
        $current = AvailabilityLogEntry::factory()->create(['training_place_id' => $this->place->id, 'event' => 'added', 'slot_from' => '2026-05-11 18:00:00', 'slot_to' => '2026-05-11 21:00:00', 'superseded_at' => null]);

        $change = new AvailabilityChange(AvailabilityChangeType::Removed, $row, Carbon::parse('2026-05-11 18:00', 'UTC'), Carbon::parse('2026-05-11 21:00', 'UTC'), null, null);

        $result = app(AvailabilityService::class)->applyChanges($this->studentMember->id, [$change]);

        $this->assertSame(1, $result['removed']);
        $this->assertFalse($result['refused']);
        $this->assertSame(0, Availability::where('student_id', $this->studentMember->id)->count());
        $this->assertNotNull($current->fresh()->superseded_at);
        $this->assertSame(1, AvailabilityLogEntry::where('training_place_id', $this->place->id)->count());
    }

    #[Test]
    public function it_ignores_unchanged_changes_entirely(): void
    {
        $row = Availability::factory()->forStudent($this->studentMember->id)->create(['date' => '2026-05-11', 'from' => '18:00:00', 'to' => '21:00:00', 'type' => 'S']);
        $current = AvailabilityLogEntry::factory()->create(['training_place_id' => $this->place->id, 'event' => 'added', 'slot_from' => '2026-05-11 18:00:00', 'slot_to' => '2026-05-11 21:00:00', 'superseded_at' => null]);

        $change = new AvailabilityChange(AvailabilityChangeType::Unchanged, $row, Carbon::parse('2026-05-11 18:00', 'UTC'), Carbon::parse('2026-05-11 21:00', 'UTC'), Carbon::parse('2026-05-11 18:00', 'UTC'), Carbon::parse('2026-05-11 21:00', 'UTC'));

        $result = app(AvailabilityService::class)->applyChanges($this->studentMember->id, [$change]);

        $this->assertSame(0, $result['added']);
        $this->assertSame(0, $result['edited']);
        $this->assertSame(0, $result['merged']);
        $this->assertSame(0, $result['removed']);
        $this->assertFalse($result['refused']);
        $this->assertSame(1, Availability::where('student_id', $this->studentMember->id)->count());
        $this->assertNull($current->fresh()->superseded_at);
        $this->assertSame(1, AvailabilityLogEntry::where('training_place_id', $this->place->id)->count());
    }

    #[Test]
    public function it_writes_nothing_and_refuses_when_the_result_would_overlap_a_row_it_was_not_given(): void
    {
        Availability::factory()->forStudent($this->studentMember->id)->create(['date' => '2026-05-11', 'from' => '19:00:00', 'to' => '20:00:00', 'type' => 'S']);

        Log::spy();

        $change = new AvailabilityChange(AvailabilityChangeType::Added, null, null, null, Carbon::parse('2026-05-11 18:00', 'UTC'), Carbon::parse('2026-05-11 21:00', 'UTC'));

        $result = app(AvailabilityService::class)->applyChanges($this->studentMember->id, [$change]);

        $this->assertTrue($result['refused']);
        $this->assertSame(0, $result['added']);
        $this->assertSame(0, $result['edited']);
        $this->assertSame(0, $result['merged']);
        $this->assertSame(0, $result['removed']);
        $this->assertSame(1, Availability::where('student_id', $this->studentMember->id)->count());
        $this->assertSame(0, AvailabilityLogEntry::where('training_place_id', $this->place->id)->count());
        Log::shouldHaveReceived('warning')->once();
    }

    #[Test]
    public function it_reads_saved_rows_for_a_date_range(): void
    {
        Availability::factory()->forStudent($this->studentMember->id)->create(['date' => '2026-05-10', 'from' => '18:00:00', 'to' => '19:00:00', 'type' => 'S']);
        Availability::factory()->forStudent($this->studentMember->id)->create(['date' => '2026-05-11', 'from' => '20:00:00', 'to' => '21:00:00', 'type' => 'S']);
        Availability::factory()->forStudent($this->studentMember->id)->create(['date' => '2026-05-11', 'from' => '18:00:00', 'to' => '19:00:00', 'type' => 'S']);
        Availability::factory()->forStudent($this->studentMember->id)->create(['date' => '2026-05-13', 'from' => '18:00:00', 'to' => '19:00:00', 'type' => 'S']);

        $rows = app(AvailabilityService::class)->availabilityRowsFor(
            $this->studentMember->id,
            Carbon::parse('2026-05-11', 'UTC'),
            Carbon::parse('2026-05-12', 'UTC'),
        );

        $this->assertCount(2, $rows);
        $this->assertSame(['2026-05-11', '2026-05-11'], $rows->map(fn (Availability $row) => $row->date->format('Y-m-d'))->all());
        $this->assertSame(['18:00:00', '20:00:00'], $rows->map(fn (Availability $row) => $row->from->format('H:i:s'))->all());
    }

    #[Test]
    public function it_does_not_refuse_when_two_untouched_rows_already_overlap(): void
    {
        Availability::factory()->forStudent($this->studentMember->id)->create(['date' => '2026-05-11', 'from' => '09:00:00', 'to' => '12:00:00', 'type' => 'S']);
        Availability::factory()->forStudent($this->studentMember->id)->create(['date' => '2026-05-11', 'from' => '11:00:00', 'to' => '14:00:00', 'type' => 'S']);

        $change = new AvailabilityChange(AvailabilityChangeType::Added, null, null, null, Carbon::parse('2026-05-12 18:00', 'UTC'), Carbon::parse('2026-05-12 21:00', 'UTC'));

        $result = app(AvailabilityService::class)->applyChanges($this->studentMember->id, [$change]);

        $this->assertFalse($result['refused']);
        $this->assertSame(1, $result['added']);
        $this->assertSame(3, Availability::where('student_id', $this->studentMember->id)->count());
    }

    #[Test]
    public function it_allows_abutting_ranges_through_the_overlap_guard(): void
    {
        Availability::factory()->forStudent($this->studentMember->id)->create(['date' => '2026-05-11', 'from' => '18:00:00', 'to' => '19:00:00', 'type' => 'S']);

        $change = new AvailabilityChange(AvailabilityChangeType::Added, null, null, null, Carbon::parse('2026-05-11 19:00', 'UTC'), Carbon::parse('2026-05-11 20:00', 'UTC'));

        $result = app(AvailabilityService::class)->applyChanges($this->studentMember->id, [$change]);

        $this->assertFalse($result['refused']);
        $this->assertSame(1, $result['added']);
        $this->assertSame(2, Availability::where('student_id', $this->studentMember->id)->count());
    }

    #[Test]
    public function it_refuses_a_change_beyond_the_future_window(): void
    {
        $change = new AvailabilityChange(AvailabilityChangeType::Added, null, null, null, Carbon::parse('2026-06-14 18:00', 'UTC'), Carbon::parse('2026-06-14 21:00', 'UTC'));

        $result = app(AvailabilityService::class)->applyChanges($this->studentMember->id, [$change]);

        $this->assertTrue($result['refused']);
        $this->assertSame(0, Availability::where('student_id', $this->studentMember->id)->count());
    }

    #[Test]
    public function it_refuses_add_slots_where_a_range_ends_past_the_window(): void
    {
        $result = app(AvailabilityService::class)->addSlots($this->studentMember->id, [
            [Carbon::parse('2026-06-13 18:00', 'UTC'), Carbon::parse('2026-06-13 21:00', 'UTC')],
            [Carbon::parse('2026-06-14 18:00', 'UTC'), Carbon::parse('2026-06-14 21:00', 'UTC')],
        ]);

        $this->assertTrue($result['refused']);
        $this->assertSame(0, Availability::where('student_id', $this->studentMember->id)->count());
    }

    #[Test]
    public function it_accepts_a_slot_on_the_last_day_of_the_window(): void
    {
        $result = app(AvailabilityService::class)->addSlots($this->studentMember->id, [
            [Carbon::parse('2026-06-13 18:00', 'UTC'), Carbon::parse('2026-06-13 21:00', 'UTC')],
        ]);

        $this->assertFalse($result['refused']);
        $this->assertSame(1, Availability::where('student_id', $this->studentMember->id)->count());
    }
}
