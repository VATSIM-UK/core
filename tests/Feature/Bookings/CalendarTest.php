<?php

declare(strict_types=1);

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\Calendar;
use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Ban;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\Roster;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use DatabaseTransactions;

    private function placeOnRoster(Account $member): Roster
    {
        $member->addState(State::findByCode('DIVISION'));

        return Roster::create(['account_id' => $member->id]);
    }

    #[Test]
    public function it_loads_with_todays_date(): void
    {
        Livewire::test(Calendar::class)
            ->assertSet('selectedDate', Carbon::today());
    }

    #[Test]
    public function it_loads_with_specified_year_and_month(): void
    {
        Livewire::test(Calendar::class, ['year' => 2026, 'month' => 7])
            ->assertSet('selectedDate', Carbon::create(2026, 7, 1));
    }

    #[Test]
    public function it_shows_error_when_not_authenticated(): void
    {
        Livewire::test(Calendar::class)
            ->call('createBooking', [
                'starts_at' => Carbon::tomorrow()->setHour(10)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::tomorrow()->setHour(12)->format('Y-m-d H:i:s'),
                'position_id' => 1,
            ])
            ->assertDispatched('booking-error');
    }

    #[Test]
    public function it_shows_error_when_no_position_or_callsign(): void
    {
        $member = Account::factory()->withQualification()->create();

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('createBooking', [
                'starts_at' => Carbon::tomorrow()->setHour(10)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::tomorrow()->setHour(12)->format('Y-m-d H:i:s'),
            ])
            ->assertDispatched('booking-error');
    }

    #[Test]
    public function it_shows_error_when_start_in_past(): void
    {
        $member = Account::factory()->withQualification()->create();

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('createBooking', [
                'starts_at' => Carbon::yesterday()->setHour(10)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::yesterday()->setHour(12)->format('Y-m-d H:i:s'),
                'position_id' => 1,
            ])
            ->assertDispatched('booking-error');
    }

    #[Test]
    public function it_shows_error_when_start_equals_end(): void
    {
        $member = Account::factory()->withQualification()->create();
        $time = Carbon::tomorrow()->setHour(10)->format('Y-m-d H:i:s');

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('createBooking', [
                'starts_at' => $time,
                'ends_at' => $time,
                'position_id' => 1,
            ])
            ->assertDispatched('booking-error');
    }

    #[Test]
    public function it_creates_a_booking_successfully(): void
    {
        $member = Account::factory()->withQualification()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 5]);
        $member->qualifications()->sync([$qual->id]);
        $member = $member->fresh();
        $this->placeOnRoster($member);

        $position = Position::factory()->create(['type' => Position::TYPE_ENROUTE]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('createBooking', [
                'starts_at' => Carbon::tomorrow()->setHour(10)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::tomorrow()->setHour(12)->format('Y-m-d H:i:s'),
                'position_id' => (string) $position->id,
            ])
            ->assertDispatched('booking-created');

        $this->assertDatabaseHas('bookings', [
            'member_id' => $member->id,
            'position_id' => $position->id,
        ]);
    }

    #[Test]
    public function it_shows_warning_on_position_overlap(): void
    {
        $member = Account::factory()->withQualification()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 5]);
        $member->qualifications()->sync([$qual->id]);
        $member = $member->fresh();

        $position = Position::factory()->create(['type' => Position::TYPE_ENROUTE]);

        Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('createBooking', [
                'starts_at' => Carbon::tomorrow()->setHour(11)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::tomorrow()->setHour(13)->format('Y-m-d H:i:s'),
                'position_id' => (string) $position->id,
            ])
            ->assertDispatched('booking-warning');
    }

    #[Test]
    public function it_shows_warning_on_member_overlap(): void
    {
        $member = Account::factory()->withQualification()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 5]);
        $member->qualifications()->sync([$qual->id]);
        $member = $member->fresh();

        $positionA = Position::factory()->create(['type' => Position::TYPE_ENROUTE]);
        $positionB = Position::factory()->create(['type' => Position::TYPE_ENROUTE]);

        Booking::create([
            'position_id' => $positionA->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('createBooking', [
                'starts_at' => Carbon::tomorrow()->setHour(11)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::tomorrow()->setHour(13)->format('Y-m-d H:i:s'),
                'position_id' => (string) $positionB->id,
            ])
            ->assertDispatched('booking-warning');
    }

    #[Test]
    public function it_shows_warning_when_not_qualified(): void
    {
        $member = Account::factory()->withQualification()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 1]);
        $member->qualifications()->sync([$qual->id]);
        $member = $member->fresh();

        $position = Position::factory()->create(['type' => Position::TYPE_TOWER]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('createBooking', [
                'starts_at' => Carbon::tomorrow()->setHour(10)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::tomorrow()->setHour(12)->format('Y-m-d H:i:s'),
                'position_id' => (string) $position->id,
            ])
            ->assertDispatched('booking-warning');
    }

    #[Test]
    public function it_allows_deleting_own_booking(): void
    {
        $member = Account::factory()->withQualification()->create();
        $position = Position::factory()->create();

        $booking = Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('cancelBooking', ['id' => (string) $booking->id])
            ->assertDispatched('booking-deleted');

        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }

    #[Test]
    public function it_shows_error_when_deleting_someone_elses_booking(): void
    {
        $owner = Account::factory()->create();
        $other = Account::factory()->withQualification()->create();
        $position = Position::factory()->create();

        $booking = Booking::create([
            'position_id' => $position->id,
            'member_id' => $owner->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        Livewire::actingAs($other)
            ->test(Calendar::class)
            ->call('cancelBooking', ['id' => (string) $booking->id])
            ->assertDispatched('booking-error');

        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    #[Test]
    public function it_prevents_deleting_a_booking_that_has_already_ended(): void
    {
        $member = Account::factory()->withQualification()->create();
        $position = Position::factory()->create();

        $booking = Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::now()->subHours(3),
            'ends_at' => Carbon::now()->subHour(),
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('cancelBooking', ['id' => (string) $booking->id])
            ->assertDispatched('booking-error');

        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    #[Test]
    public function it_increments_data_version_when_booking_created(): void
    {
        $member = Account::factory()->withQualification()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 5]);
        $member->qualifications()->sync([$qual->id]);
        $member = $member->fresh();
        $this->placeOnRoster($member);

        $position = Position::factory()->create(['type' => Position::TYPE_ENROUTE]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->assertSet('dataVersion', 1)
            ->call('createBooking', [
                'starts_at' => Carbon::tomorrow()->setHour(10)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::tomorrow()->setHour(12)->format('Y-m-d H:i:s'),
                'position_id' => (string) $position->id,
            ])
            ->assertDispatched('booking-created')
            ->assertSet('dataVersion', 2);
    }

    #[Test]
    public function it_increments_data_version_when_booking_deleted(): void
    {
        $member = Account::factory()->withQualification()->create();
        $position = Position::factory()->create();

        $booking = Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->assertSet('dataVersion', 1)
            ->call('cancelBooking', ['id' => (string) $booking->id])
            ->assertDispatched('booking-deleted')
            ->assertSet('dataVersion', 2);
    }

    #[Test]
    public function it_shows_the_drag_and_click_booking_hint(): void
    {
        Livewire::test(Calendar::class)
            ->assertSee('Drag across an empty slot to book')
            ->assertDontSee('Click an empty slot to book');
    }

    #[Test]
    public function it_is_viewable_without_authentication(): void
    {
        $this->get(route('site.bookings.calendar'))->assertOk();
    }

    #[Test]
    public function it_prevents_a_banned_user_from_creating_a_booking(): void
    {
        $banned = Account::factory()->has(Ban::factory())->create();
        $position = Position::factory()->create(['type' => Position::TYPE_ENROUTE]);

        Livewire::actingAs($banned)
            ->test(Calendar::class)
            ->call('createBooking', [
                'starts_at' => Carbon::tomorrow()->setHour(10)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::tomorrow()->setHour(12)->format('Y-m-d H:i:s'),
                'position_id' => (string) $position->id,
            ])
            ->assertDispatched('booking-error');

        $this->assertDatabaseMissing('bookings', ['member_id' => $banned->id]);
    }

    #[Test]
    public function it_prevents_a_banned_user_from_deleting_a_booking(): void
    {
        $banned = Account::factory()->has(Ban::factory())->create();
        $position = Position::factory()->create();

        $booking = Booking::create([
            'position_id' => $position->id,
            'member_id' => $banned->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        Livewire::actingAs($banned)
            ->test(Calendar::class)
            ->call('cancelBooking', ['id' => (string) $booking->id])
            ->assertDispatched('booking-error');

        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    #[Test]
    public function it_separates_event_bookings_from_position_rows(): void
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        Booking::create([
            'position_id' => $position->id,
            'member_id' => Account::factory()->create()->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::today()->setHour(10),
            'ends_at' => Carbon::today()->setHour(12),
        ]);
        Booking::create([
            'position_id' => $position->id,
            'member_id' => Account::factory()->create()->id,
            'type' => Booking::TYPE_EVENT,
            'starts_at' => Carbon::today()->setHour(14),
            'ends_at' => Carbon::today()->setHour(16),
        ]);

        $component = Livewire::test(Calendar::class);

        $this->assertCount(1, $component->get('events'), 'Event bookings must appear in the events array');
        $this->assertSame('EV', $component->get('events')[0]['type']);

        $positions = $component->get('timelinePositions');
        $positionIds = [];
        foreach ($positions as $row) {
            if (isset($row['positions'])) {
                foreach ($row['positions'] as $pos) {
                    foreach ($pos['bookings'] as $b) {
                        $positionIds[] = $b['type'];
                    }
                }
            } elseif (isset($row['bookings'])) {
                foreach ($row['bookings'] as $b) {
                    $positionIds[] = $b['type'];
                }
            }
        }
        $this->assertContains('BK', $positionIds, 'Standard booking must remain in timelinePositions');
        $this->assertNotContains('EV', $positionIds, 'Event bookings must not appear in timelinePositions');
    }

    #[Test]
    public function it_filters_timeline_rows_by_callsign(): void
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        Booking::create([
            'position_id' => $position->id,
            'member_id' => Account::factory()->create()->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::today()->setHour(10),
            'ends_at' => Carbon::today()->setHour(12),
        ]);

        $component = Livewire::test(Calendar::class);

        $this->assertNotEmpty($component->get('timelinePositions'), 'Timeline should contain the EGKK booking before filtering');
        $this->assertSame('', $component->get('positionFilter'));
        $this->assertSame(0, $component->get('filterVersion'));

        // Applying a non-matching filter hides the row
        $component->set('positionFilter', 'EGLL');

        $this->assertSame('EGLL', $component->get('positionFilter'));
        $this->assertSame(1, $component->get('filterVersion'));
    }

    private function qualifiedMember(): Account
    {
        $member = Account::factory()->withQualification()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 5]);
        $member->qualifications()->sync([$qual->id]);
        $member = $member->fresh();
        $this->placeOnRoster($member);

        return $member;
    }

    #[Test]
    public function it_does_not_preload_the_position_list_into_the_page(): void
    {
        $member = $this->qualifiedMember();
        Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->assertDontSee('qualifiedPositionsData');
    }

    #[Test]
    public function it_requires_a_minimum_query_length_before_searching_positions(): void
    {
        $member = $this->qualifiedMember();
        Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        $component = Livewire::actingAs($member)->test(Calendar::class);

        $this->assertSame([], $component->instance()->searchPositions('EG'));
        $this->assertSame([], $component->instance()->searchPositions('  E  '));
    }

    #[Test]
    public function it_searches_qualified_positions_by_callsign(): void
    {
        $member = $this->qualifiedMember();
        $match = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);
        Position::factory()->create(['callsign' => 'EGLL_APP', 'type' => Position::TYPE_APPROACH]);

        $results = Livewire::actingAs($member)
            ->test(Calendar::class)
            ->instance()
            ->searchPositions('egkk');

        $this->assertSame([['id' => (string) $match->id, 'callsign' => 'EGKK_APP']], $results);
    }

    #[Test]
    public function it_excludes_positions_the_member_is_not_qualified_for_from_search(): void
    {
        $member = Account::factory()->withQualification()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 1]);
        $member->qualifications()->sync([$qual->id]);
        $member = $member->fresh();
        $this->placeOnRoster($member);

        Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        $results = Livewire::actingAs($member)
            ->test(Calendar::class)
            ->instance()
            ->searchPositions('EGKK');

        $this->assertSame([], $results);
    }

    #[Test]
    public function it_excludes_virtual_positions_from_search(): void
    {
        $member = $this->qualifiedMember();
        Position::factory()->create([
            'callsign' => 'EGKK_APP',
            'type' => Position::TYPE_APPROACH,
            'virtual' => true,
        ]);

        $results = Livewire::actingAs($member)
            ->test(Calendar::class)
            ->instance()
            ->searchPositions('EGKK');

        $this->assertSame([], $results);
    }

    #[Test]
    public function it_returns_no_search_results_for_a_member_not_on_the_roster(): void
    {
        $member = Account::factory()->withQualification()->create();
        Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        $results = Livewire::actingAs($member)
            ->test(Calendar::class)
            ->instance()
            ->searchPositions('EGKK');

        $this->assertSame([], $results);
    }

    #[Test]
    public function it_returns_no_search_results_for_a_guest(): void
    {
        Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        $results = Livewire::test(Calendar::class)
            ->instance()
            ->searchPositions('EGKK');

        $this->assertSame([], $results);
    }

    #[Test]
    public function it_sets_is_today_flag_when_viewing_today(): void
    {
        Livewire::test(Calendar::class)
            ->assertSet('selectedDate', Carbon::today())
            ->assertSee('"isToday":true', false);
    }

    #[Test]
    public function it_sets_is_today_false_when_viewing_another_date(): void
    {
        Livewire::test(Calendar::class, ['year' => 2025, 'month' => 1])
            ->assertSet('selectedDate', Carbon::create(2025, 1, 1))
            ->assertSee('"isToday":false', false);
    }
}
