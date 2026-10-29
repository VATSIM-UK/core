<?php

namespace Tests\Feature\TrainingPanel\MyTraining;

use App\Filament\Training\Pages\MyTraining\MyAvailability;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\Availability;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class MyAvailabilityFormTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    protected Account $studentAccount;

    protected Member $studentMember;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::create(2026, 5, 10, 12, 0, 0));

        $this->studentAccount = Account::factory()->create();
        $this->studentMember = Member::factory()->recycle($this->studentAccount)->create([
            'cid' => $this->studentAccount->id,
        ]);

        $this->studentAccount->givePermissionTo('training.access');

        TrainingPlace::factory()->createQuietly([
            'account_id' => $this->studentAccount->id,
        ]);
    }

    #[Test]
    public function it_only_shows_availability_for_the_authenticated_member(): void
    {
        Availability::factory()->forAccount($this->studentAccount->id)->onDate(now()->addDay()->toDateString())->timeRange('19:00:00', '21:00:00')->create();

        $otherAccount = Account::factory()->create();
        $otherMember = Member::factory()->recycle($otherAccount)->create(['cid' => $otherAccount->id]);

        Availability::factory()->forAccount($otherAccount->id)->onDate(now()->addDays(2)->toDateString())->timeRange('19:30:00', '21:30:00')->create();

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('mode', 'form')
            ->assertSuccessful();

        $this->assertCount(1, $component->instance()->getTable()->getRecords());
    }

    #[Test]
    public function it_only_shows_future_availability_slots(): void
    {
        Availability::factory()->forAccount($this->studentAccount->id)->onDate(now()->subDay()->toDateString())->timeRange('19:00:00', '21:00:00')->create();

        Availability::factory()->forAccount($this->studentAccount->id)->onDate(now()->toDateString())->timeRange('09:00:00', '11:00:00')->create();

        Availability::factory()->forAccount($this->studentAccount->id)->onDate(now()->addDay()->toDateString())->timeRange('19:30:00', '21:30:00')->create();

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('mode', 'form')
            ->assertSuccessful();

        $records = $component->instance()->getTable()->getRecords();

        $this->assertCount(1, $records);
        $this->assertEquals(now()->addDay()->toDateString(), $records->first()->starts_at->toDateString());
    }

    #[Test]
    public function it_adds_a_single_availability_slot(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('mode', 'form')
            ->set('slotData.date_range', ['start' => $tomorrow, 'end' => $tomorrow])
            ->set('slotData.from', '18:00')
            ->set('slotData.to', '21:00')
            ->call('create');

        $this->assertCount(1, Availability::where('account_id', $this->studentAccount->id)->get());
    }

    #[Test]
    public function it_shows_notification_when_slot_is_shorter_than_minimum_duration(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('mode', 'form')
            ->set('slotData.date_range', ['start' => $tomorrow, 'end' => $tomorrow])
            ->set('slotData.from', '18:00')
            ->set('slotData.to', '18:30')
            ->call('create')
            ->assertNotified();

        $this->assertCount(0, Availability::where('account_id', $this->studentAccount->id)->get());
    }

    #[Test]
    public function it_adds_multiple_slots_across_a_date_range(): void
    {
        $start = now()->addDay()->toDateString();
        $end = now()->addDays(5)->toDateString();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('mode', 'form')
            ->set('slotData.date_range', ['start' => $start, 'end' => $end])
            ->set('slotData.from', '18:00')
            ->set('slotData.to', '21:00')
            ->call('create');

        $this->assertCount(5, Availability::where('account_id', $this->studentAccount->id)->get());
    }

    #[Test]
    public function it_merges_a_new_slot_that_overlaps_an_existing_one(): void
    {
        $date = now()->addDay()->toDateString();

        Availability::factory()->forAccount($this->studentAccount->id)->onDate($date)->timeRange('18:00:00', '20:00:00')->create();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('mode', 'form')
            ->set('slotData.date_range', ['start' => $date, 'end' => $date])
            ->set('slotData.from', '19:00')
            ->set('slotData.to', '21:00')
            ->call('create');

        $slots = Availability::where('account_id', $this->studentAccount->id)->get();

        $this->assertCount(1, $slots, 'Should have merged into one slot, not created a second');
        $this->assertEquals("{$date} 18:00:00", $slots->first()->getRawOriginal('starts_at'));
        $this->assertEquals("{$date} 21:00:00", $slots->first()->getRawOriginal('ends_at'));
    }

    #[Test]
    public function it_expands_an_existing_slot_when_new_slot_starts_earlier(): void
    {
        $date = now()->addDay()->toDateString();

        Availability::factory()->forAccount($this->studentAccount->id)->onDate($date)->timeRange('19:00:00', '21:00:00')->create();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('mode', 'form')
            ->set('slotData.date_range', ['start' => $date, 'end' => $date])
            ->set('slotData.from', '17:00')
            ->set('slotData.to', '20:00')
            ->call('create');

        $slots = Availability::where('account_id', $this->studentAccount->id)->get();

        $this->assertCount(1, $slots);
        $this->assertEquals("{$date} 17:00:00", $slots->first()->getRawOriginal('starts_at'));
        $this->assertEquals("{$date} 21:00:00", $slots->first()->getRawOriginal('ends_at'));
    }

    #[Test]
    public function it_expands_an_existing_slot_when_new_slot_ends_later(): void
    {
        $date = now()->addDay()->toDateString();

        Availability::factory()->forAccount($this->studentAccount->id)->onDate($date)->timeRange('17:00:00', '19:00:00')->create();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('mode', 'form')
            ->set('slotData.date_range', ['start' => $date, 'end' => $date])
            ->set('slotData.from', '18:00')
            ->set('slotData.to', '22:00')
            ->call('create');

        $slots = Availability::where('account_id', $this->studentAccount->id)->get();

        $this->assertCount(1, $slots);
        $this->assertEquals("{$date} 17:00:00", $slots->first()->getRawOriginal('starts_at'));
        $this->assertEquals("{$date} 22:00:00", $slots->first()->getRawOriginal('ends_at'));
    }

    #[Test]
    public function it_does_not_duplicate_when_new_slot_is_fully_contained_within_existing(): void
    {
        $date = now()->addDay()->toDateString();

        Availability::factory()->forAccount($this->studentAccount->id)->onDate($date)->timeRange('17:00:00', '22:00:00')->create();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('mode', 'form')
            ->set('slotData.date_range', ['start' => $date, 'end' => $date])
            ->set('slotData.from', '18:00')
            ->set('slotData.to', '21:00')
            ->call('create');

        $slots = Availability::where('account_id', $this->studentAccount->id)->get();

        $this->assertCount(1, $slots);
        $this->assertEquals("{$date} 17:00:00", $slots->first()->getRawOriginal('starts_at'));
        $this->assertEquals("{$date} 22:00:00", $slots->first()->getRawOriginal('ends_at'));
    }

    #[Test]
    public function it_does_not_merge_slots_belonging_to_other_members(): void
    {
        $date = now()->addDay()->toDateString();

        $otherAccount = Account::factory()->create();
        $otherMember = Member::factory()->recycle($otherAccount)->create(['cid' => $otherAccount->id]);

        Availability::factory()->forAccount($otherAccount->id)->onDate($date)->timeRange('18:00:00', '20:00:00')->create();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('mode', 'form')
            ->set('slotData.date_range', ['start' => $date, 'end' => $date])
            ->set('slotData.from', '19:00')
            ->set('slotData.to', '21:00')
            ->call('create');

        $this->assertCount(1, Availability::where('account_id', $this->studentAccount->id)->get());
        $this->assertCount(1, Availability::where('account_id', $otherAccount->id)->get());
    }

    #[Test]
    public function it_stores_times_as_utc(): void
    {
        $date = now()->addDay()->toDateString();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('mode', 'form')
            ->set('slotData.date_range', ['start' => $date, 'end' => $date])
            ->set('slotData.from', '12:00')
            ->set('slotData.to', '14:00')
            ->call('create');

        $availability = Availability::where('account_id', $this->studentAccount->id)->first();

        $this->assertNotNull($availability);
        $this->assertEquals("{$date} 12:00:00", $availability->getRawOriginal('starts_at'));
        $this->assertEquals("{$date} 14:00:00", $availability->getRawOriginal('ends_at'));
    }

    #[Test]
    public function it_merges_a_new_slot_that_bridges_two_existing_ones_into_one(): void
    {
        $date = now()->addDay()->toDateString();

        foreach ([['17:00:00', '18:30:00'], ['20:00:00', '22:00:00']] as [$from, $to]) {
            Availability::factory()->forAccount($this->studentAccount->id)->onDate($date)->timeRange($from, $to)->create();
        }

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('mode', 'form')
            ->set('slotData.date_range', ['start' => $date, 'end' => $date])
            ->set('slotData.from', '18:00')
            ->set('slotData.to', '21:00')
            ->call('create');

        $slot = Availability::where('account_id', $this->studentAccount->id)->sole();

        $this->assertEquals("{$date} 17:00:00", $slot->getRawOriginal('starts_at'));
        $this->assertEquals("{$date} 22:00:00", $slot->getRawOriginal('ends_at'));
    }

    #[Test]
    public function it_refuses_adding_a_slot_beyond_the_future_window(): void
    {
        $beyond = now()->addDays(32)->toDateString();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('mode', 'form')
            ->set('slotData.date_range', ['start' => $beyond, 'end' => $beyond])
            ->set('slotData.from', '18:00')
            ->set('slotData.to', '21:00')
            ->call('create')
            ->assertNotified('You can only add availability up to 31 days ahead.');

        $this->assertCount(0, Availability::where('account_id', $this->studentAccount->id)->get());
    }
}
