<?php

namespace Tests\Feature\TrainingPanel\MyTraining;

use App\Filament\Training\Pages\MyTraining\MyAvailability;
use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Models\Cts\PositionValidation;
use App\Models\Cts\Position;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class MyAvailabilityTest extends BaseTrainingPanelTestCase
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
    public function it_loads_for_member_with_training_access(): void
    {
        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_does_not_load_without_training_access(): void
    {
        $noAccessAccount = Account::factory()->create();
        Member::factory()->recycle($noAccessAccount)->create(['cid' => $noAccessAccount->id]);

        $this->actingAs($noAccessAccount)
            ->get('/training/my-availability')
            ->assertNotFound();
    }

    #[Test]
    public function it_does_not_load_with_training_access_but_no_training_place(): void
    {
        $accountWithPermission = Account::factory()->create();
        Member::factory()->recycle($accountWithPermission)->create(['cid' => $accountWithPermission->id]);
        $accountWithPermission->givePermissionTo('training.access');

        $this->actingAs($accountWithPermission)
            ->get('/training/my-availability')
            ->assertForbidden();
    }

    #[Test]
    public function it_loads_with_training_access_and_pilot_position_validation_but_no_training_place(): void
    {
        $accountWithValidation = Account::factory()->create();
        $member = Member::factory()->recycle($accountWithValidation)->create(['cid' => $accountWithValidation->id]);
        $accountWithValidation->givePermissionTo('training.access');

        $pilotPosition = Position::factory()->create([
            'callsign' => 'P1_PPL(A)',
        ]);

        PositionValidation::factory()->create([
            'member_id' => $member->id,
            'position_id' => $pilotPosition->id,
        ]);

        $this->actingAs($accountWithValidation)
            ->get('/training/my-availability')
            ->assertSuccessful();
    }

    #[Test]
    public function it_does_not_load_with_training_access_and_non_pilot_position_validation_only(): void
    {
        $accountWithNonPilot = Account::factory()->create();
        $member = Member::factory()->recycle($accountWithNonPilot)->create(['cid' => $accountWithNonPilot->id]);
        $accountWithNonPilot->givePermissionTo('training.access');

        $atcPosition = Position::factory()->create([
            'callsign' => 'EGKK_APP',
        ]);

        PositionValidation::factory()->create([
            'member_id' => $member->id,
            'position_id' => $atcPosition->id,
        ]);

        $this->actingAs($accountWithNonPilot)
            ->get('/training/my-availability')
            ->assertForbidden();
    }

    #[Test]
    public function it_only_shows_availability_for_the_authenticated_member(): void
    {
        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => now()->addDay()->toDateString(),
            'from' => '19:00:00',
            'to' => '21:00:00',
            'type' => 'S',
        ]);

        $otherAccount = Account::factory()->create();
        $otherMember = Member::factory()->recycle($otherAccount)->create(['cid' => $otherAccount->id]);

        Availability::factory()->forStudent($otherMember->id)->create([
            'date' => now()->addDays(2)->toDateString(),
            'from' => '19:30:00',
            'to' => '21:30:00',
            'type' => 'S',
        ]);

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->assertSuccessful();

        $this->assertCount(1, $component->instance()->getTable()->getRecords());
    }

    #[Test]
    public function it_only_shows_future_availability_slots(): void
    {
        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => now()->subDay()->toDateString(),
            'from' => '19:00:00',
            'to' => '21:00:00',
            'type' => 'S',
        ]);

        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => now()->toDateString(),
            'from' => '09:00:00',
            'to' => '11:00:00',
            'type' => 'S',
        ]);

        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => now()->addDay()->toDateString(),
            'from' => '19:30:00',
            'to' => '21:30:00',
            'type' => 'S',
        ]);

        $component = Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->assertSuccessful();

        $records = $component->instance()->getTable()->getRecords();

        $this->assertCount(1, $records);
        $this->assertEquals(now()->addDay()->toDateString(), $records->first()->date->toDateString());
    }

    #[Test]
    public function it_adds_a_single_availability_slot(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.date_range', ['start' => $tomorrow, 'end' => $tomorrow])
            ->set('data.from', '18:00')
            ->set('data.to', '21:00')
            ->call('create');

        $this->assertCount(1, Availability::where('student_id', $this->studentMember->id)->get());
    }

    #[Test]
    public function it_adds_multiple_slots_across_a_date_range(): void
    {
        $start = now()->addDay()->toDateString();
        $end = now()->addDays(5)->toDateString();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.date_range', ['start' => $start, 'end' => $end])
            ->set('data.from', '18:00')
            ->set('data.to', '21:00')
            ->call('create');

        $this->assertCount(5, Availability::where('student_id', $this->studentMember->id)->get());
    }

    #[Test]
    public function it_merges_a_new_slot_that_overlaps_an_existing_one(): void
    {
        $date = now()->addDay()->toDateString();

        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $date,
            'from' => '18:00:00',
            'to' => '20:00:00',
            'type' => 'S',
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.date_range', ['start' => $date, 'end' => $date])
            ->set('data.from', '19:00')
            ->set('data.to', '21:00')
            ->call('create');

        $slots = Availability::where('student_id', $this->studentMember->id)->get();

        $this->assertCount(1, $slots, 'Should have merged into one slot, not created a second');
        $this->assertEquals('18:00:00', $slots->first()->getRawOriginal('from'));
        $this->assertEquals('21:00:00', $slots->first()->getRawOriginal('to'));
    }

    #[Test]
    public function it_expands_an_existing_slot_when_new_slot_starts_earlier(): void
    {
        $date = now()->addDay()->toDateString();

        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $date,
            'from' => '19:00:00',
            'to' => '21:00:00',
            'type' => 'S',
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.date_range', ['start' => $date, 'end' => $date])
            ->set('data.from', '17:00')
            ->set('data.to', '20:00')
            ->call('create');

        $slots = Availability::where('student_id', $this->studentMember->id)->get();

        $this->assertCount(1, $slots);
        $this->assertEquals('17:00:00', $slots->first()->getRawOriginal('from'));
        $this->assertEquals('21:00:00', $slots->first()->getRawOriginal('to'));
    }

    #[Test]
    public function it_expands_an_existing_slot_when_new_slot_ends_later(): void
    {
        $date = now()->addDay()->toDateString();

        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $date,
            'from' => '17:00:00',
            'to' => '19:00:00',
            'type' => 'S',
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.date_range', ['start' => $date, 'end' => $date])
            ->set('data.from', '18:00')
            ->set('data.to', '22:00')
            ->call('create');

        $slots = Availability::where('student_id', $this->studentMember->id)->get();

        $this->assertCount(1, $slots);
        $this->assertEquals('17:00:00', $slots->first()->getRawOriginal('from'));
        $this->assertEquals('22:00:00', $slots->first()->getRawOriginal('to'));
    }

    #[Test]
    public function it_does_not_duplicate_when_new_slot_is_fully_contained_within_existing(): void
    {
        $date = now()->addDay()->toDateString();

        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $date,
            'from' => '17:00:00',
            'to' => '22:00:00',
            'type' => 'S',
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.date_range', ['start' => $date, 'end' => $date])
            ->set('data.from', '18:00')
            ->set('data.to', '21:00')
            ->call('create');

        $slots = Availability::where('student_id', $this->studentMember->id)->get();

        $this->assertCount(1, $slots);
        $this->assertEquals('17:00:00', $slots->first()->getRawOriginal('from'));
        $this->assertEquals('22:00:00', $slots->first()->getRawOriginal('to'));
    }

    #[Test]
    public function it_does_not_merge_slots_belonging_to_other_members(): void
    {
        $date = now()->addDay()->toDateString();

        $otherAccount = Account::factory()->create();
        $otherMember = Member::factory()->recycle($otherAccount)->create(['cid' => $otherAccount->id]);

        Availability::factory()->forStudent($otherMember->id)->create([
            'date' => $date,
            'from' => '18:00:00',
            'to' => '20:00:00',
            'type' => 'S',
        ]);

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->set('data.date_range', ['start' => $date, 'end' => $date])
            ->set('data.from', '19:00')
            ->set('data.to', '21:00')
            ->call('create');

        $this->assertCount(1, Availability::where('student_id', $this->studentMember->id)->get());
        $this->assertCount(1, Availability::where('student_id', $otherMember->id)->get());
    }

    #[Test]
    public function it_correctly_converts_availability_creation_to_utc(): void
    {
        config(['app.timezone' => 'UTC']);
        $tz = 'America/New_York';
        session(['availability_timezone' => $tz]);

        $knownUtcTime = Carbon::create(2026, 12, 25, 10, 0, 0, 'UTC');
        $this->travelTo($knownUtcTime);
        $date = '2026-12-25';

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->assertSet('timezone', $tz)
            ->set('data.date_range', ['start' => $date, 'end' => $date])
            ->set('data.from', '12:00')
            ->set('data.to', '14:00')
            ->call('create');

        $availability = Availability::where('student_id', $this->studentMember->id)->first();

        $this->assertNotNull($availability);
        $this->assertEquals('17:00:00', $availability->getRawOriginal('from'), 'from should be stored as UTC');
        $this->assertEquals('19:00:00', $availability->getRawOriginal('to'), 'to should be stored as UTC');
    }
}
