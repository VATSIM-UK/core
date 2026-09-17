<?php

declare(strict_types=1);

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\Calendar;
use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Events\Event;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalendarWeekViewTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_defaults_to_day_view_mode(): void
    {
        Livewire::test(Calendar::class)
            ->assertSet('viewMode', 'day');
    }

    #[Test]
    public function it_switches_to_week_view_mode(): void
    {
        Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertSet('viewMode', 'week');
    }

    #[Test]
    public function it_falls_back_to_day_mode_for_an_unrecognised_mode(): void
    {
        Livewire::test(Calendar::class)
            ->call('setViewMode', 'bogus')
            ->assertSet('viewMode', 'day');
    }

    #[Test]
    public function it_only_includes_bookings_within_the_seven_day_window(): void
    {
        // Default window (selectedDate = today) is today-3 .. today+3.
        $position = Position::factory()->create();

        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => Carbon::today()->subDays(3)->setHour(9),
            'ends_at' => Carbon::today()->subDays(3)->setHour(10),
        ]);
        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => Carbon::today()->addDays(3)->setHour(9),
            'ends_at' => Carbon::today()->addDays(3)->setHour(10),
        ]);
        $outsideBefore = Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => Carbon::today()->subDays(4)->setHour(9),
            'ends_at' => Carbon::today()->subDays(4)->setHour(10),
        ]);
        $outsideAfter = Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => Carbon::today()->addDays(4)->setHour(9),
            'ends_at' => Carbon::today()->addDays(4)->setHour(10),
        ]);

        $weekBookings = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->get('weekBookings');

        $this->assertCount(1, $weekBookings[Carbon::today()->subDays(3)->toDateString()]);
        $this->assertCount(1, $weekBookings[Carbon::today()->addDays(3)->toDateString()]);

        $allIds = collect($weekBookings)->flatten(1)->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->assertNotContains((string) $outsideBefore->id, $allIds);
        $this->assertNotContains((string) $outsideAfter->id, $allIds);
    }

    #[Test]
    public function it_discards_event_type_bookings_with_a_position_from_the_week_grid(): void
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        Booking::factory()->create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_EVENT,
            'starts_at' => Carbon::today()->setHour(14),
            'ends_at' => Carbon::today()->setHour(16),
        ]);

        $weekBookings = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->get('weekBookings');

        $this->assertSame([], collect($weekBookings)->flatten(1)->all(), 'A booking on a real position during an event must not appear at all');
    }

    #[Test]
    public function it_includes_published_events_in_the_week_grid(): void
    {
        Event::factory()->published()->create([
            'name' => 'UK Controller Meet',
            'start' => Carbon::today()->addDays(2)->setHour(19),
            'end' => Carbon::today()->addDays(2)->setHour(21),
        ]);

        $weekBookings = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->get('weekBookings');

        $dayEntries = $weekBookings[Carbon::today()->addDays(2)->toDateString()];

        $this->assertCount(1, $dayEntries);
        $this->assertSame('EV', $dayEntries[0]['type']);
        $this->assertSame('UK Controller Meet', $dayEntries[0]['event_name']);
    }

    #[Test]
    public function it_switches_to_day_mode_and_jumps_when_viewing_a_week_booking(): void
    {
        $booking = Booking::factory()->create([
            'starts_at' => Carbon::today()->addDays(3)->setHour(9),
            'ends_at' => Carbon::today()->addDays(3)->setHour(10),
        ]);

        Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertSet('viewMode', 'week')
            ->call('viewBookingFromWeek', $booking->starts_at->toDateString(), 'core', $booking->id, null)
            ->assertSet('viewMode', 'day')
            ->assertSet('selectedDate', $booking->starts_at->copy()->startOfDay())
            ->assertDispatched('scroll-to-booking', source: 'core', id: $booking->id, ctsBookingId: null, instant: true);
    }

    #[Test]
    public function it_shows_the_day_and_week_toggle_buttons(): void
    {
        Livewire::test(Calendar::class)
            ->assertSee('Day')
            ->assertSee('Week');
    }

    #[Test]
    public function it_hides_date_navigation_and_position_search_in_week_mode(): void
    {
        Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertDontSee('Previous day')
            ->assertDontSee('Search callsign...');
    }

    #[Test]
    public function it_shows_date_navigation_and_position_search_in_day_mode(): void
    {
        Livewire::test(Calendar::class)
            ->assertSee('Previous day')
            ->assertSee('Search callsign...');
    }

    #[Test]
    public function it_shows_the_week_grid_instead_of_the_timeline_in_week_mode(): void
    {
        // Not assertDontSee('Position') -- the create modal has its own "Position" label, always rendered.
        Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertDontSee('Drag across an empty slot to book')
            ->assertDontSee('No positions available for this date.')
            ->assertSee('No bookings');
    }

    #[Test]
    public function it_still_shows_the_timeline_in_day_mode(): void
    {
        // Asserting a real booking's callsign, not "Position" -- see the comment above.
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(10),
        ]);

        Livewire::test(Calendar::class)
            ->assertSee('Drag across an empty slot to book')
            ->assertSee('EGKK_APP');
    }

    #[Test]
    public function it_lists_a_weeks_bookings_in_the_week_grid(): void
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(10),
        ]);

        Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertSee('EGKK_APP')
            ->assertSee('09:00');
    }

    #[Test]
    public function it_shows_the_event_name_as_the_blocks_label_in_the_week_grid(): void
    {
        Event::factory()->published()->create([
            'name' => 'UK Controller Meet',
            'start' => Carbon::today()->setHour(19),
            'end' => Carbon::today()->setHour(21),
        ]);

        Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertSee('UK Controller Meet');
    }

    #[Test]
    public function it_shows_the_day_and_week_toggle_buttons_in_week_mode(): void
    {
        Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertSee('Day')
            ->assertSee('Week');
    }

    #[Test]
    public function it_switches_to_day_mode_and_dispatches_the_scroll_event_for_a_booking_already_on_the_selected_date(): void
    {
        $booking = Booking::factory()->create([
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(10),
        ]);

        Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertSet('viewMode', 'week')
            ->call('viewBookingFromWeek', $booking->starts_at->toDateString(), 'core', $booking->id, null)
            ->assertSet('viewMode', 'day')
            ->assertSet('selectedDate', $booking->starts_at->copy()->startOfDay())
            ->assertDispatched('scroll-to-booking', source: 'core', id: $booking->id, ctsBookingId: null, instant: true);
    }

    #[Test]
    public function it_shows_the_full_position_name_for_an_unmerged_booking(): void
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        $booking = Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(10),
        ]);

        $blocks = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->instance()
            ->buildWeekDayBlocks(Carbon::today()->toDateString());

        $this->assertCount(1, $blocks);
        $this->assertSame('EGKK_APP', $blocks[0]['label']);
        $this->assertSame(1, $blocks[0]['count']);
        $this->assertSame((int) $booking->id, $blocks[0]['id']);
        $this->assertSame('core', $blocks[0]['source']);
        $this->assertSame('09:00', $blocks[0]['from']);
        $this->assertSame('10:00', $blocks[0]['to']);
    }

    #[Test]
    public function it_merges_overlapping_bookings_on_the_same_aerodrome_into_one_block(): void
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(11),
        ]);
        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => Carbon::today()->setHour(10),
            'ends_at' => Carbon::today()->setHour(12),
        ]);

        $blocks = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->instance()
            ->buildWeekDayBlocks(Carbon::today()->toDateString());

        $this->assertCount(1, $blocks);
        $this->assertSame(2, $blocks[0]['count']);
        $this->assertSame('EGKK', $blocks[0]['label'], 'A merged block shows the aerodrome, not one position\'s full name');
        $this->assertNull($blocks[0]['id'], 'A merged block cannot point at a single booking');
        $this->assertSame('09:00', $blocks[0]['from']);
        $this->assertSame('12:00', $blocks[0]['to']);
    }

    #[Test]
    public function it_does_not_merge_non_overlapping_bookings_on_the_same_aerodrome(): void
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => Carbon::today()->setHour(6),
            'ends_at' => Carbon::today()->setHour(7),
        ]);
        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(10),
        ]);

        $blocks = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->instance()
            ->buildWeekDayBlocks(Carbon::today()->toDateString());

        $this->assertCount(2, $blocks);
        $this->assertSame([1, 1], array_column($blocks, 'count'));
    }

    #[Test]
    public function it_does_not_merge_overlapping_bookings_on_different_aerodromes(): void
    {
        $egkk = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);
        $egll = Position::factory()->create(['callsign' => 'EGLL_APP', 'type' => Position::TYPE_APPROACH]);

        Booking::factory()->create([
            'position_id' => $egkk->id,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(11),
        ]);
        Booking::factory()->create([
            'position_id' => $egll->id,
            'starts_at' => Carbon::today()->setHour(10),
            'ends_at' => Carbon::today()->setHour(12),
        ]);

        $blocks = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->instance()
            ->buildWeekDayBlocks(Carbon::today()->toDateString());

        $this->assertCount(2, $blocks, 'Clashing aerodromes must stay as separate entries, not merge into one');
        $this->assertSame(['EGKK_APP', 'EGLL_APP'], array_column($blocks, 'label'));
        $this->assertSame([1, 1], array_column($blocks, 'count'));
    }

    #[Test]
    public function it_does_not_merge_two_different_overlapping_events(): void
    {
        Event::factory()->published()->create([
            'name' => 'Event One',
            'start' => Carbon::today()->setHour(19),
            'end' => Carbon::today()->setHour(21),
        ]);
        Event::factory()->published()->create([
            'name' => 'Event Two',
            'start' => Carbon::today()->setHour(20),
            'end' => Carbon::today()->setHour(22),
        ]);

        $blocks = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->instance()
            ->buildWeekDayBlocks(Carbon::today()->toDateString());

        $this->assertCount(2, $blocks);
        $this->assertSame(['Event One', 'Event Two'], array_column($blocks, 'label'));
        $this->assertSame([1, 1], array_column($blocks, 'count'));
    }

    #[Test]
    public function it_shows_event_blocks_before_booking_blocks_regardless_of_start_time(): void
    {
        $date = Carbon::today()->addYears(2);
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => $date->copy()->setHour(6),
            'ends_at' => $date->copy()->setHour(7),
        ]);
        Event::factory()->published()->create([
            'name' => 'Test Event',
            'start' => $date->copy()->setHour(19),
            'end' => $date->copy()->setHour(21),
        ]);

        $blocks = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->call('jumpToDate', $date->toDateString())
            ->instance()
            ->buildWeekDayBlocks($date->toDateString());

        $this->assertCount(2, $blocks);
        $this->assertSame('Test Event', $blocks[0]['label'], 'Event must render above bookings even though it starts later');
        $this->assertSame('EGKK_APP', $blocks[1]['label']);
    }

    #[Test]
    public function it_centers_the_week_window_three_days_before_the_selected_date(): void
    {
        $component = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->call('jumpToDate', Carbon::today()->addDays(10)->toDateString());

        $expectedStart = Carbon::today()->addDays(7); // selected date (today+10) minus 3
        $this->assertTrue($expectedStart->isSameDay($component->instance()->weekWindowStart()));
    }

    #[Test]
    public function it_allows_the_week_window_to_show_past_days(): void
    {
        $component = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->call('jumpToDate', Carbon::yesterday()->toDateString());

        $expectedStart = Carbon::yesterday()->subDays(3);
        $this->assertTrue($expectedStart->isSameDay($component->instance()->weekWindowStart()));
    }

    #[Test]
    public function it_stays_in_week_mode_after_navigating_with_jump_to_date(): void
    {
        $component = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->call('jumpToDate', Carbon::today()->addDays(10)->toDateString());

        $component->assertSet('viewMode', 'week');
    }
}
