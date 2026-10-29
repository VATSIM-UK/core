<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\MyTraining;

use App\Filament\Training\Pages\MyTraining\MyAvailability;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\Availability;
use App\Models\Training\TrainingPlace\AvailabilityLogEntry;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class MyAvailabilitySaveTest extends BaseTrainingPanelTestCase
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
        $this->studentMember = Member::factory()->recycle($this->studentAccount)->create([
            'cid' => $this->studentAccount->id,
        ]);

        $this->studentAccount->givePermissionTo('training.access');

        $this->place = TrainingPlace::factory()->createQuietly([
            'account_id' => $this->studentAccount->id,
        ]);
    }

    /**
     * A run of $count consecutive 15-minute cell keys starting at $from.
     *
     * @return array<int, string>
     */
    private function cells(string $date, string $from, int $count): array
    {
        $out = [];
        $start = Carbon::parse("{$date} {$from}", 'UTC');
        for ($i = 0; $i < $count; $i++) {
            $out[] = $start->copy()->addMinutes(15 * $i)->format('Y-m-d H:i');
        }

        return $out;
    }

    #[Test]
    public function it_saves_a_painted_range_as_a_new_slot(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.availability', $this->cells($tomorrow, '18:00', 12))
            ->call('save')
            ->assertReturned(true);

        $row = Availability::where('account_id', $this->studentAccount->id)->sole();
        $this->assertSame('18:00:00', Carbon::parse($row->getRawOriginal('starts_at'))->format('H:i:s'));
        $this->assertSame('21:00:00', Carbon::parse($row->getRawOriginal('ends_at'))->format('H:i:s'));
    }

    #[Test]
    public function it_replaces_the_whole_visited_day_including_erasing(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        Availability::factory()->forAccount($this->studentAccount->id)->onDate($tomorrow)->timeRange('09:00:00', '12:00:00')->create();

        $erased = AvailabilityLogEntry::factory()->create([
            'training_place_id' => $this->place->id,
            'event' => 'added',
            'slot_from' => "{$tomorrow} 09:00:00",
            'slot_to' => "{$tomorrow} 12:00:00",
            'created_at' => now()->subDay(),
            'superseded_at' => null,
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.availability', $this->cells($tomorrow, '18:00', 12))
            ->call('save');

        $remaining = Availability::where('account_id', $this->studentAccount->id)->sole();
        $this->assertSame('18:00:00', Carbon::parse($remaining->getRawOriginal('starts_at'))->format('H:i:s'));
        $this->assertSame('21:00:00', Carbon::parse($remaining->getRawOriginal('ends_at'))->format('H:i:s'));

        $this->assertNotNull($erased->fresh()->superseded_at, 'Erasing a slot must supersede its current log entry.');
        $this->assertSame(
            1,
            AvailabilityLogEntry::where('training_place_id', $this->place->id)->whereNull('superseded_at')->count(),
        );
    }

    #[Test]
    public function it_merges_two_saved_slots_into_one(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        Availability::factory()->forAccount($this->studentAccount->id)->onDate($tomorrow)->timeRange('18:00:00', '19:00:00')->create();
        Availability::factory()->forAccount($this->studentAccount->id)->onDate($tomorrow)->timeRange('20:00:00', '21:00:00')->create();

        $first = AvailabilityLogEntry::factory()->create([
            'training_place_id' => $this->place->id,
            'event' => 'added',
            'slot_from' => "{$tomorrow} 18:00:00",
            'slot_to' => "{$tomorrow} 19:00:00",
            'created_at' => now()->subDay(),
            'superseded_at' => null,
        ]);
        $second = AvailabilityLogEntry::factory()->create([
            'training_place_id' => $this->place->id,
            'event' => 'added',
            'slot_from' => "{$tomorrow} 20:00:00",
            'slot_to' => "{$tomorrow} 21:00:00",
            'created_at' => now()->subDay(),
            'superseded_at' => null,
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.availability', $this->cells($tomorrow, '18:00', 12))
            ->call('save');

        $survivor = Availability::where('account_id', $this->studentAccount->id)->sole();
        $this->assertSame('18:00:00', Carbon::parse($survivor->getRawOriginal('starts_at'))->format('H:i:s'));
        $this->assertSame('21:00:00', Carbon::parse($survivor->getRawOriginal('ends_at'))->format('H:i:s'));

        $this->assertNotNull($first->fresh()->superseded_at);
        $this->assertNotNull($second->fresh()->superseded_at);
        $this->assertSame(
            1,
            AvailabilityLogEntry::where('training_place_id', $this->place->id)->where('event', 'merged')->count(),
        );
    }

    #[Test]
    public function it_skips_and_reports_runs_under_forty_five_minutes(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.availability', $this->cells($tomorrow, '18:00', 2))
            ->call('save')
            ->assertReturned(true);

        $this->assertSame(0, Availability::where('account_id', $this->studentAccount->id)->count());
    }

    #[Test]
    public function it_round_trips_a_midnight_ending_slot_without_churn(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.availability', $this->cells($tomorrow, '22:00', 8))
            ->call('save');

        $row = Availability::where('account_id', $this->studentAccount->id)->sole();
        $this->assertSame('00:00:00', Carbon::parse($row->getRawOriginal('ends_at'))->format('H:i:s'));

        $component->call('save');

        $this->assertSame(1, AvailabilityLogEntry::count(), 'A second save must not rewrite an untouched slot.');
    }

    #[Test]
    public function it_writes_nothing_when_nothing_is_painted(): void
    {
        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->call('save')
            ->assertReturned(true);

        $this->assertSame(0, Availability::where('account_id', $this->studentAccount->id)->count());
        $this->assertSame(0, AvailabilityLogEntry::count());
    }

    #[Test]
    public function it_does_not_touch_a_week_the_student_never_opened(): void
    {
        Availability::factory()->forAccount($this->studentAccount->id)->onDate(now()->addWeeks(3)->toDateString())->timeRange('18:00:00', '21:00:00')->create();

        $tomorrow = now()->addDay()->toDateString();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.availability', $this->cells($tomorrow, '18:00', 4))
            ->call('save');

        $this->assertSame(2, Availability::where('account_id', $this->studentAccount->id)->count());
    }

    #[Test]
    public function it_accumulates_paint_across_weeks_and_keeps_it_on_the_way_back(): void
    {
        $thisWeek = now()->toDateString();
        $nextWeek = now()->addWeek()->toDateString();

        $component = Livewire::actingAs($this->studentAccount)->test(MyAvailability::class)
            ->set('data.availability', $this->cells($thisWeek, '18:00', 4))
            ->call('nextWeek');

        $component->set('data.availability', array_merge(
            $component->get('data.availability'),
            $this->cells($nextWeek, '19:00', 4),
        ));

        $component->call('save');

        $this->assertSame(2, Availability::where('account_id', $this->studentAccount->id)->count());
    }

    #[Test]
    public function it_does_not_delete_a_row_added_after_the_page_was_seeded(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        Availability::factory()->forAccount($this->studentAccount->id)->onDate($tomorrow)->timeRange('18:00:00', '21:00:00')->create();

        $component = Livewire::actingAs($this->studentAccount)->test(MyAvailability::class);

        $this->assertSame(
            $this->cells($tomorrow, '18:00', 12),
            array_values($component->get('data.availability')),
        );

        $late = Availability::factory()->forAccount($this->studentAccount->id)->onDate($tomorrow)->timeRange('22:00:00', '23:00:00')->create();

        $component->call('save')->assertReturned(true);

        $this->assertSame(2, Availability::where('account_id', $this->studentAccount->id)->count());
        $this->assertNotNull($late->fresh());
    }

    #[Test]
    public function it_keeps_a_row_the_grid_cannot_reproduce_through_an_unrelated_save(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        $odd = Availability::factory()->forAccount($this->studentAccount->id)->onDate($tomorrow)->timeRange('18:05:00', '21:00:00')->create();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.availability', $this->cells($tomorrow, '22:00', 4))
            ->call('save')
            ->assertReturned(true);

        $this->assertSame(2, Availability::where('account_id', $this->studentAccount->id)->count());
        $this->assertNotNull($odd->fresh());
    }

    #[Test]
    public function it_refuses_and_reports_a_conflict_when_a_row_changes_mid_edit(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        $component = Livewire::actingAs($this->studentAccount)->test(MyAvailability::class);

        Availability::factory()->forAccount($this->studentAccount->id)->onDate($tomorrow)->timeRange('18:05:00', '21:00:00')->create();

        $component
            ->set('data.availability', $this->cells($tomorrow, '18:00', 12))
            ->call('save')
            ->assertReturned(false)
            ->assertNotified(
                Notification::make()
                    ->title('Nothing was saved')
                    ->body('Some of this date changed while you were editing it. Refresh to see the latest availability, then try again.')
                    ->danger(),
            );

        $this->assertSame(1, Availability::where('account_id', $this->studentAccount->id)->count());
    }

    #[Test]
    public function it_keeps_a_row_added_elsewhere_on_an_open_date_after_switching_weeks(): void
    {
        $friday = now()->addDays(2)->toDateString();

        $component = Livewire::actingAs($this->studentAccount)->test(MyAvailability::class);

        $other = Availability::factory()->forAccount($this->studentAccount->id)->onDate($friday)->timeRange('18:00:00', '20:00:00')->create();

        $component->call('nextWeek')->call('save');

        $this->assertNotNull($other->fresh());
    }

    #[Test]
    public function it_shows_one_past_week_read_only_and_keeps_its_rows_on_save(): void
    {
        $lastWeek = now()->subWeek()->toDateString();

        $past = Availability::factory()->forAccount($this->studentAccount->id)->onDate($lastWeek)->timeRange('18:00:00', '20:00:00')->create();

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->call('previousWeek')
            ->call('previousWeek')
            ->assertSet('weekOffset', -1);

        $this->assertContains("{$lastWeek} 18:00", $component->instance()->lockedCells());

        $component->set('data.availability', $this->cells($lastWeek, '10:00', 4))->call('save');

        $this->assertNotNull($past->fresh());
        $this->assertSame(1, Availability::where('account_id', $this->studentAccount->id)->count());
    }

    #[Test]
    public function it_does_not_delete_a_row_added_elsewhere_between_autosaves(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.availability', $this->cells($tomorrow, '10:00', 4))
            ->call('save');

        $other = Availability::factory()->forAccount($this->studentAccount->id)->onDate($tomorrow)->timeRange('18:00:00', '20:00:00')->create();

        $component->set('data.availability', $this->cells($tomorrow, '12:00', 4))->call('save');

        $this->assertNotNull($other->fresh());
        $remaining = Availability::where('account_id', $this->studentAccount->id)->where('id', '!=', $other->id)->sole();
        $this->assertSame('12:00:00', Carbon::parse($remaining->getRawOriginal('starts_at'))->format('H:i:s'));
    }

    #[Test]
    public function it_keeps_unsaved_short_runs_painted_after_saving(): void
    {
        $tomorrow = now()->addDay()->toDateString();
        $cells = $this->cells($tomorrow, '18:00', 2);

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.availability', $cells)
            ->call('save')
            ->assertSet('data.availability', $cells);
    }

    #[Test]
    public function it_confirms_before_pasting_over_a_week(): void
    {
        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->mountAction('pasteWeek')
            ->assertActionMounted('pasteWeek')
            ->callMountedAction()
            ->assertDispatched('availability-paste-week');
    }

    #[Test]
    public function it_does_not_save_paint_beyond_the_future_window(): void
    {
        $component = Livewire::actingAs($this->studentAccount)->test(MyAvailability::class);

        $component->call('nextWeek')->call('nextWeek')->call('nextWeek')->call('nextWeek')
            ->assertSet('weekOffset', 4);

        // The 2026-06-14 run is 60 minutes, so only the cap can drop it.
        $painted = array_merge(
            $component->get('data.availability'),
            $this->cells('2026-06-13', '18:00', 4),
            $this->cells('2026-06-14', '18:00', 4),
        );

        $component->set('data.availability', $painted)->call('save')->assertReturned(true);

        $rows = Availability::where('account_id', $this->studentAccount->id)->get();
        $this->assertSame(1, $rows->count());
        $this->assertSame('2026-06-13', $rows->first()->starts_at->toDateString());
        $this->assertSame('18:00:00', Carbon::parse($rows->first()->getRawOriginal('starts_at'))->format('H:i:s'));
        $this->assertSame('19:00:00', Carbon::parse($rows->first()->getRawOriginal('ends_at'))->format('H:i:s'));
    }
}
