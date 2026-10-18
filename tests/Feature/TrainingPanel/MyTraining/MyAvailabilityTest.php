<?php

namespace Tests\Feature\TrainingPanel\MyTraining;

use App\Filament\Training\Pages\MyTraining\MyAvailability;
use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Models\Cts\Position;
use App\Models\Cts\PositionValidation;
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

        $this->travelTo(Carbon::create(2026, 5, 13, 12, 0, 0));

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
            ->get('/training/my-training/availability')
            ->assertNotFound();
    }

    #[Test]
    public function it_does_not_load_with_training_access_but_no_training_place(): void
    {
        $accountWithPermission = Account::factory()->create();
        Member::factory()->recycle($accountWithPermission)->create(['cid' => $accountWithPermission->id]);
        $accountWithPermission->givePermissionTo('training.access');

        $this->actingAs($accountWithPermission)
            ->get('/training/my-training/availability')
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
            ->get('/training/my-training/availability')
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
            ->get('/training/my-training/availability')
            ->assertForbidden();
    }

    #[Test]
    public function it_seeds_the_current_week_with_saved_slots(): void
    {
        $tomorrow = now()->addDay()->toDateString();
        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $tomorrow, 'from' => '18:00:00', 'to' => '21:00:00', 'type' => 'S',
        ]);

        $component = Livewire::actingAs($this->studentAccount)->test(MyAvailability::class)->assertSuccessful();

        $seeded = $component->get('data.availability');
        $this->assertCount(12, $seeded);
        $this->assertContains("{$tomorrow} 18:00", $seeded);
        $this->assertContains("{$tomorrow} 20:45", $seeded);
    }

    #[Test]
    public function it_seeds_only_its_own_members_slots(): void
    {
        $other = Account::factory()->create();
        $otherMember = Member::factory()->recycle($other)->create(['cid' => $other->id]);
        Availability::factory()->forStudent($otherMember->id)->create([
            'date' => now()->addDay()->toDateString(), 'from' => '18:00:00', 'to' => '21:00:00', 'type' => 'S',
        ]);

        $component = Livewire::actingAs($this->studentAccount)->test(MyAvailability::class)->assertSuccessful();

        $this->assertSame([], $component->get('data.availability'));
    }

    #[Test]
    public function it_renders_a_cell_for_every_quarter_hour_of_the_window(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->assertSuccessful()
            ->assertSeeHtml("data-slot=\"{$tomorrow} 06:00\"")
            ->assertSeeHtml("data-slot=\"{$tomorrow} 23:45\"")
            ->assertSeeHtml('Show overnight')
            ->assertSeeHtml('data-overnight="0"')
            ->assertDontSeeHtml('data-slot="2026-05-18 06:00"');
    }

    #[Test]
    public function it_shows_the_calendar_by_default_and_the_form_when_toggled(): void
    {
        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->assertSuccessful()
            ->assertSee('All changes saved')
            ->assertDontSee('Date(s)')
            ->assertDontSee('No availability added yet')
            ->set('mode', 'form')
            ->assertSee('Date(s)')
            ->assertSee('Change default times')
            ->assertSee('No availability added yet')
            ->assertDontSee('All changes saved');
    }

    #[Test]
    public function it_opens_overnight_when_a_saved_slot_starts_before_the_window(): void
    {
        $tomorrow = now()->addDay()->toDateString();
        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $tomorrow, 'from' => '06:00:00', 'to' => '09:00:00', 'type' => 'S',
        ]);

        $component = Livewire::actingAs($this->studentAccount)->test(MyAvailability::class)->assertSuccessful();

        $component->assertSeeHtml('data-overnight="1"');
        $this->assertTrue($component->instance()->hasOvernightSlots());
    }

    #[Test]
    public function it_opens_overnight_when_a_saved_slot_runs_past_the_window(): void
    {
        $tomorrow = now()->addDay()->toDateString();
        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $tomorrow, 'from' => '20:00:00', 'to' => '22:30:00', 'type' => 'S',
        ]);

        $component = Livewire::actingAs($this->studentAccount)->test(MyAvailability::class)->assertSuccessful();

        $component->assertSeeHtml('data-overnight="1"');
        $this->assertTrue($component->instance()->hasOvernightSlots());
    }

    #[Test]
    public function it_seeds_nothing_for_a_week_it_has_not_opened(): void
    {
        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => now()->addWeeks(3)->toDateString(), 'from' => '18:00:00', 'to' => '21:00:00', 'type' => 'S',
        ]);

        $component = Livewire::actingAs($this->studentAccount)->test(MyAvailability::class)->assertSuccessful();

        $this->assertSame([], $component->get('data.availability'));
    }

    #[Test]
    public function it_hides_a_saved_row_that_cannot_be_reproduced_on_the_grid(): void
    {
        $tomorrow = now()->addDay()->toDateString();
        Availability::factory()->forStudent($this->studentMember->id)->create([
            'date' => $tomorrow, 'from' => '18:05:00', 'to' => '21:00:00', 'type' => 'S',
        ]);

        $component = Livewire::actingAs($this->studentAccount)->test(MyAvailability::class)->assertSuccessful();

        $this->assertSame([], $component->get('data.availability'));
        $this->assertContains("{$tomorrow} 18:00", $component->instance()->lockedCells());
    }

    #[Test]
    public function it_stops_forward_navigation_at_the_future_window(): void
    {
        $component = Livewire::actingAs($this->studentAccount)->test(MyAvailability::class);

        $component->call('nextWeek')->call('nextWeek')->call('nextWeek')->call('nextWeek')
            ->assertSet('weekOffset', 4)
            ->call('nextWeek')
            ->assertSet('weekOffset', 4);
    }

    #[Test]
    public function it_reports_the_maximum_reachable_week(): void
    {
        $component = Livewire::actingAs($this->studentAccount)->test(MyAvailability::class);

        $this->assertSame(4, $component->instance()->maxWeekOffset());
    }

    #[Test]
    public function it_shows_the_outside_the_window_legend(): void
    {
        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->assertSuccessful()
            ->assertSee('Outside the window');
    }

    #[Test]
    public function it_marks_days_beyond_the_window_as_unpaintable(): void
    {
        Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->call('nextWeek')->call('nextWeek')->call('nextWeek')->call('nextWeek')
            ->assertSeeHtml('title="Outside the window"');
    }

    #[Test]
    public function it_does_not_present_beyond_window_days_as_editable(): void
    {
        $html = Livewire::actingAs($this->studentAccount)
            ->test(MyAvailability::class)
            ->call('nextWeek')->call('nextWeek')->call('nextWeek')->call('nextWeek')
            ->html();

        preg_match('/<div data-slot="2026-06-14 18:00"[^>]*>/', $html, $matches);

        $this->assertNotEmpty($matches, 'The beyond-window cell should render.');
        $this->assertStringNotContainsString('cursor-pointer', $matches[0]);
    }
}
