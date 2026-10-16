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
            ->assertNotified('Availability saved');

        $row = Availability::where('student_id', $this->studentMember->id)->sole();
        $this->assertSame('18:00:00', $row->getRawOriginal('from'));
        $this->assertSame('21:00:00', $row->getRawOriginal('to'));
    }

    #[Test]
    public function it_replaces_the_whole_visited_day_including_erasing(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $tomorrow, 'from' => '09:00:00', 'to' => '12:00:00', 'type' => 'S',
        ]);

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

        $remaining = Availability::where('student_id', $this->studentMember->id)->sole();
        $this->assertSame('18:00:00', $remaining->getRawOriginal('from'));
        $this->assertSame('21:00:00', $remaining->getRawOriginal('to'));

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

        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $tomorrow, 'from' => '18:00:00', 'to' => '19:00:00', 'type' => 'S',
        ]);
        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $tomorrow, 'from' => '20:00:00', 'to' => '21:00:00', 'type' => 'S',
        ]);

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

        $survivor = Availability::where('student_id', $this->studentMember->id)->sole();
        $this->assertSame('18:00:00', $survivor->getRawOriginal('from'));
        $this->assertSame('21:00:00', $survivor->getRawOriginal('to'));

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
            ->assertNotified('Availability saved - 1 selection was under 45 minutes');

        $this->assertSame(0, Availability::where('student_id', $this->studentMember->id)->count());
    }

    #[Test]
    public function it_round_trips_a_midnight_ending_slot_without_churn(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.availability', $this->cells($tomorrow, '22:00', 8))
            ->call('save');

        $row = Availability::where('student_id', $this->studentMember->id)->sole();
        $this->assertSame('00:00:00', $row->getRawOriginal('to'));

        $component->call('save');

        $this->assertSame(1, AvailabilityLogEntry::count(), 'A second save must not rewrite an untouched slot.');
    }

    #[Test]
    public function it_writes_nothing_when_nothing_is_painted(): void
    {
        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->call('save')
            ->assertNotified('No changes to save');

        $this->assertSame(0, Availability::where('student_id', $this->studentMember->id)->count());
        $this->assertSame(0, AvailabilityLogEntry::count());
    }

    #[Test]
    public function it_does_not_touch_a_week_the_student_never_opened(): void
    {
        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => now()->addWeeks(3)->toDateString(), 'from' => '18:00:00', 'to' => '21:00:00', 'type' => 'S',
        ]);

        $tomorrow = now()->addDay()->toDateString();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.availability', $this->cells($tomorrow, '18:00', 4))
            ->call('save');

        $this->assertSame(2, Availability::where('student_id', $this->studentMember->id)->count());
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

        $this->assertSame(2, Availability::where('student_id', $this->studentMember->id)->count());
    }

    #[Test]
    public function it_does_not_delete_a_row_added_after_the_page_was_seeded(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $tomorrow, 'from' => '18:00:00', 'to' => '21:00:00', 'type' => 'S',
        ]);

        $component = Livewire::actingAs($this->studentAccount)->test(MyAvailability::class);

        $this->assertSame(
            $this->cells($tomorrow, '18:00', 12),
            array_values($component->get('data.availability')),
        );

        $late = Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $tomorrow, 'from' => '22:00:00', 'to' => '23:00:00', 'type' => 'S',
        ]);

        $component->call('save')->assertNotified('No changes to save');

        $this->assertSame(2, Availability::where('student_id', $this->studentMember->id)->count());
        $this->assertNotNull($late->fresh());
    }

    #[Test]
    public function it_keeps_a_row_the_grid_cannot_reproduce_through_an_unrelated_save(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        $odd = Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $tomorrow, 'from' => '18:05:00', 'to' => '21:00:00', 'type' => 'S',
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.availability', $this->cells($tomorrow, '22:00', 4))
            ->call('save')
            ->assertNotified('Availability saved');

        $this->assertSame(2, Availability::where('student_id', $this->studentMember->id)->count());
        $this->assertNotNull($odd->fresh());
    }

    #[Test]
    public function it_refuses_and_reports_a_conflict_when_a_row_changes_mid_edit(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        $component = Livewire::actingAs($this->studentAccount)->test(MyAvailability::class);

        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $tomorrow, 'from' => '18:05:00', 'to' => '21:00:00', 'type' => 'S',
        ]);

        $component
            ->set('data.availability', $this->cells($tomorrow, '18:00', 12))
            ->call('save')
            ->assertNotified(
                Notification::make()
                    ->title('Nothing was saved')
                    ->body('Some of this date changed while you were editing it. Refresh to see the latest availability, then try again.')
                    ->danger(),
            );

        $this->assertSame(1, Availability::where('student_id', $this->studentMember->id)->count());
    }

    #[Test]
    public function it_keeps_a_row_added_elsewhere_on_an_open_date_after_switching_weeks(): void
    {
        $friday = now()->addDays(2)->toDateString();

        $component = Livewire::actingAs($this->studentAccount)->test(MyAvailability::class);

        $other = Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $friday, 'from' => '18:00:00', 'to' => '20:00:00', 'type' => 'S',
        ]);

        $component->call('nextWeek')->call('save');

        $this->assertNotNull($other->fresh());
    }
}
