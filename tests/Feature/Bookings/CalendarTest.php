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
    public function it_rejects_end_time_before_start_time(): void
    {
        $member = Account::factory()->withQualification()->create();
        $this->placeOnRoster($member);
        $position = Position::factory()->create(['type' => Position::TYPE_ENROUTE]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('createBooking', [
                'starts_at' => Carbon::tomorrow()->setTime(12, 0)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::tomorrow()->setTime(10, 0)->format('Y-m-d H:i:s'),
                'position_id' => (string) $position->id,
            ])
            ->assertDispatched('booking-error');

        $this->assertDatabaseMissing('bookings', ['member_id' => $member->id]);
    }

    #[Test]
    public function it_rejects_times_not_on_fifteen_minute_boundaries(): void
    {
        $member = Account::factory()->withQualification()->create();
        $this->placeOnRoster($member);
        $position = Position::factory()->create(['type' => Position::TYPE_ENROUTE]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('createBooking', [
                'starts_at' => Carbon::tomorrow()->setTime(10, 7)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::tomorrow()->setTime(12, 0)->format('Y-m-d H:i:s'),
                'position_id' => (string) $position->id,
            ])
            ->assertDispatched('booking-error');

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('createBooking', [
                'starts_at' => Carbon::tomorrow()->setTime(10, 0)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::tomorrow()->setTime(12, 10)->format('Y-m-d H:i:s'),
                'position_id' => (string) $position->id,
            ])
            ->assertDispatched('booking-error');

        $this->assertDatabaseMissing('bookings', ['member_id' => $member->id]);
    }

    #[Test]
    public function it_accepts_times_on_fifteen_minute_boundaries(): void
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
                'starts_at' => Carbon::tomorrow()->setTime(10, 15)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::tomorrow()->setTime(11, 45)->format('Y-m-d H:i:s'),
                'position_id' => (string) $position->id,
            ])
            ->assertDispatched('booking-created');

        $this->assertDatabaseHas('bookings', [
            'member_id' => $member->id,
            'position_id' => $position->id,
        ]);
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
    public function it_labels_the_create_button_book(): void
    {
        $member = Account::factory()->withQualification()->create();

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->assertSee('</i> Book', false)
            ->assertDontSee('</i> New', false);
    }

    #[Test]
    public function it_labels_the_callsign_box_as_a_search(): void
    {
        Livewire::test(Calendar::class)
            ->assertSee('Search callsign...')
            ->assertDontSee('Filter callsign');
    }

    #[Test]
    public function it_shows_the_drag_and_click_booking_hint(): void
    {
        Livewire::test(Calendar::class)
            ->assertSee('Drag across an empty slot to book')
            ->assertDontSee('Click an empty slot to book');
    }

    #[Test]
    public function it_redirects_guests_to_login(): void
    {
        $this->get(route('site.bookings.calendar'))
            ->assertRedirect(route('landing'));
    }

    #[Test]
    public function it_forbids_non_staff_members(): void
    {
        $this->actingAs($this->user)
            ->get(route('site.bookings.calendar'))
            ->assertForbidden();
    }

    #[Test]
    public function it_allows_staff_access(): void
    {
        $this->actingAs($this->privacc)
            ->get(route('site.bookings.calendar'))
            ->assertOk();
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

    /**
     * @return list<string>
     */
    private function bookingTypesOnRows(array $timelinePositions): array
    {
        $types = [];

        foreach ($timelinePositions as $row) {
            foreach ($row['positions'] ?? [$row] as $position) {
                foreach ($position['bookings'] ?? [] as $booking) {
                    $types[] = $booking['type'];
                }
            }
        }

        return $types;
    }

    #[Test]
    public function it_discards_event_bookings_that_have_a_position(): void
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        Booking::create([
            'position_id' => $position->id,
            'member_id' => Account::factory()->create()->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::today()->setHour(10),
            'ends_at' => Carbon::today()->setHour(12),
        ]);
        // A booking on a real callsign made during an event: the controller's own
        // booking, not the event, so the calendar disregards it entirely.
        Booking::create([
            'position_id' => $position->id,
            'member_id' => Account::factory()->create()->id,
            'type' => Booking::TYPE_EVENT,
            'starts_at' => Carbon::today()->setHour(14),
            'ends_at' => Carbon::today()->setHour(16),
        ]);

        $component = Livewire::test(Calendar::class);

        $this->assertSame([], $component->get('events'), 'An event booking with a callsign is not an event');

        $types = $this->bookingTypesOnRows($component->get('timelinePositions'));
        $this->assertSame(['BK'], $types, 'Only the standard booking may remain on the position rows');
    }

    #[Test]
    public function it_ignores_discarded_event_bookings_when_scaling_the_timeline(): void
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        Booking::create([
            'position_id' => $position->id,
            'member_id' => Account::factory()->create()->id,
            'type' => Booking::TYPE_EVENT,
            'starts_at' => Carbon::today()->setHour(2),
            'ends_at' => Carbon::today()->setHour(5),
        ]);

        $hours = Livewire::test(Calendar::class)->instance()->getTimelineHours();
        $active = array_column(array_filter($hours, fn (array $h): bool => $h['type'] === 'hour'), 'hour');

        $this->assertSame([], $active, 'A discarded booking must not mark its hours as active');
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

    private function bookOn(Position $position, string $from, string $to): Booking
    {
        $startsAt = Carbon::today()->setTimeFromTimeString($from);
        $endsAt = Carbon::today()->setTimeFromTimeString($to);

        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            $endsAt->addDay();
        }

        return Booking::create([
            'position_id' => $position->id,
            'member_id' => Account::factory()->create()->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function timelineRowFor(array $timelinePositions, string $callsign): array
    {
        foreach ($timelinePositions as $row) {
            foreach ($row['positions'] ?? [$row] as $position) {
                if (($position['callsign'] ?? null) === $callsign) {
                    return $position;
                }
            }
        }

        $this->fail("No timeline row rendered for {$callsign}");
    }

    /**
     * @return array<string, mixed>
     */
    private function rowForBookings(string $callsign, array $times): array
    {
        $position = Position::factory()->create(['callsign' => $callsign, 'type' => Position::TYPE_APPROACH]);

        foreach ($times as [$from, $to]) {
            $this->bookOn($position, $from, $to);
        }

        return $this->timelineRowFor(
            Livewire::test(Calendar::class)->get('timelinePositions'),
            $callsign
        );
    }

    #[Test]
    public function it_keeps_non_overlapping_bookings_in_a_single_lane(): void
    {
        $row = $this->rowForBookings('EGKK_APP', [['08:00', '10:00'], ['12:00', '14:00']]);

        $this->assertSame(1, $row['laneCount'], 'Rows without overlap must keep their original height');
        $this->assertSame([0, 0], array_column($row['bookings'], 'lane'));
    }

    #[Test]
    public function it_treats_back_to_back_bookings_as_non_overlapping(): void
    {
        $row = $this->rowForBookings('EGKK_APP', [['10:00', '12:00'], ['12:00', '14:00']]);

        $this->assertSame(1, $row['laneCount'], 'A booking starting exactly when another ends does not overlap');
        $this->assertSame([0, 0], array_column($row['bookings'], 'lane'));
    }

    #[Test]
    public function it_stacks_overlapping_bookings_into_separate_lanes(): void
    {
        $row = $this->rowForBookings('EGKK_APP', [['10:00', '12:00'], ['11:00', '13:00']]);

        $this->assertSame(2, $row['laneCount']);
        $this->assertSame([0, 1], array_column($row['bookings'], 'lane'));
    }

    #[Test]
    public function it_uses_only_as_many_lanes_as_the_busiest_moment_needs(): void
    {
        // The first booking has finished by 13:00, so the last one reuses its lane.
        $row = $this->rowForBookings('EGKK_APP', [['10:00', '12:00'], ['11:00', '15:00'], ['13:00', '16:00']]);

        $this->assertSame(2, $row['laneCount']);
        $this->assertSame([0, 1, 0], array_column($row['bookings'], 'lane'));
    }

    #[Test]
    public function it_stacks_three_concurrent_bookings(): void
    {
        $row = $this->rowForBookings('EGKK_APP', [['10:00', '13:00'], ['11:00', '14:00'], ['12:00', '15:00']]);

        $this->assertSame(3, $row['laneCount']);
        $this->assertSame([0, 1, 2], array_column($row['bookings'], 'lane'));
    }

    #[Test]
    public function it_treats_a_booking_running_past_midnight_as_occupying_the_rest_of_the_day(): void
    {
        $row = $this->rowForBookings('EGKK_APP', [['22:00', '02:00'], ['23:00', '23:30']]);

        $this->assertSame(2, $row['laneCount'], 'The overnight booking must not read as a zero-length span');
        $this->assertSame([0, 1], array_column($row['bookings'], 'lane'));
    }

    #[Test]
    public function it_orders_row_bookings_by_start_time(): void
    {
        $row = $this->rowForBookings('EGKK_APP', [['14:00', '15:00'], ['09:00', '10:00'], ['11:00', '12:00']]);

        $this->assertSame(['09:00', '11:00', '14:00'], array_column($row['bookings'], 'from'));
    }

    /**
     * @return array<string, mixed>
     */
    private function timelineHeader(string $callsign, array $times): array
    {
        $position = Position::factory()->create(['callsign' => $callsign, 'type' => Position::TYPE_APPROACH]);

        foreach ($times as [$from, $to]) {
            $this->bookOn($position, $from, $to);
        }

        $markers = Livewire::test(Calendar::class)->instance()->getTimelineHours();

        $indexed = ['hour' => [], 'gap' => []];

        foreach ($markers as $marker) {
            $indexed[$marker['type']][$marker['hour']] = $marker;
        }

        return $indexed;
    }

    #[Test]
    public function it_bands_a_single_compressed_hour(): void
    {
        // 09:00 is a lone inactive hour. The scale squeezes it to a sixth of a
        // normal hour, so it has to be shaded as a gap rather than left looking
        // like an ordinary, slightly narrow hour.
        $header = $this->timelineHeader('EGKK_APP', [['08:00', '09:00'], ['10:00', '18:00']]);

        $this->assertArrayHasKey(9, $header['gap'], 'Even one compressed hour must render as a band');
        $this->assertSame(1, $header['gap'][9]['hours']);
        $this->assertArrayNotHasKey(9, $header['hour'], 'It must not also appear as a plain hour tick');

        $this->assertArrayHasKey(8, $header['hour']);
        $this->assertArrayHasKey(10, $header['hour']);
    }

    #[Test]
    public function it_bands_every_inactive_stretch(): void
    {
        $header = $this->timelineHeader('EGKK_APP', [['08:00', '10:00'], ['12:00', '14:00']]);

        $this->assertArrayHasKey(10, $header['gap'], 'A two hour gap is banded like any other');
        $this->assertSame(2, $header['gap'][10]['hours']);
        $this->assertArrayNotHasKey(10, $header['hour']);
        $this->assertArrayNotHasKey(11, $header['hour']);
    }

    #[Test]
    public function it_falls_back_to_the_duration_when_a_band_is_too_narrow_for_the_range(): void
    {
        $header = $this->timelineHeader('EGKK_APP', [['06:00', '13:00'], ['16:00', '23:00']]);

        $band = $header['gap'][13];
        $this->assertSame(3, $band['hours']);
        $this->assertFalse($band['show_label'], 'A narrow band cannot fit "13:00 - 16:00"');
        $this->assertTrue($band['show_short_label'], 'It must still state the compressed time');
        $this->assertSame('3h', $band['short_label']);

        $this->assertTrue($header['gap'][0]['show_label'], 'The wider overnight band still fits the full range');
    }

    #[Test]
    public function it_still_states_the_compressed_time_for_a_one_hour_band(): void
    {
        $header = $this->timelineHeader('EGKK_APP', [['08:00', '09:00'], ['10:00', '18:00']]);

        $band = $header['gap'][9];
        $this->assertFalse($band['show_label']);
        $this->assertTrue($band['show_short_label']);
        $this->assertSame('1h', $band['short_label']);
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
