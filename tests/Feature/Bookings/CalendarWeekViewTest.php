<?php

declare(strict_types=1);

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\Calendar;
use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Events\Event;
use App\Models\Mship\Account;
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
        // Anchor to a known Wednesday so the Mon-Sun window is deterministic.
        $anchor = Carbon::parse('2026-09-16');
        $weekStart = $anchor->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->addDays(6);

        $position = Position::factory()->create();

        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => $weekStart->copy()->setHour(9),
            'ends_at' => $weekStart->copy()->setHour(10),
        ]);
        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => $weekEnd->copy()->setHour(9),
            'ends_at' => $weekEnd->copy()->setHour(10),
        ]);
        $outsideBefore = Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => $weekStart->copy()->subDay()->setHour(9),
            'ends_at' => $weekStart->copy()->subDay()->setHour(10),
        ]);
        $outsideAfter = Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => $weekEnd->copy()->addDay()->setHour(9),
            'ends_at' => $weekEnd->copy()->addDay()->setHour(10),
        ]);

        $weekBookings = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->call('jumpToDate', $anchor->toDateString())
            ->get('weekBookings');

        $this->assertCount(1, $weekBookings[$weekStart->toDateString()]);
        $this->assertCount(1, $weekBookings[$weekEnd->toDateString()]);

        $allIds = collect($weekBookings)->flatten(1)->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->assertNotContains((string) $outsideBefore->id, $allIds);
        $this->assertNotContains((string) $outsideAfter->id, $allIds);
    }

    #[Test]
    public function it_filters_week_bookings_by_callsign_prefix(): void
    {
        $anchor = Carbon::parse('2026-09-16');
        $egll = Position::factory()->create(['callsign' => 'EGLL_TWR']);
        $egkk = Position::factory()->create(['callsign' => 'EGKK_APP']);

        Booking::factory()->create([
            'position_id' => $egll->id,
            'starts_at' => $anchor->copy()->setHour(9),
            'ends_at' => $anchor->copy()->setHour(10),
        ]);
        Booking::factory()->create([
            'position_id' => $egkk->id,
            'starts_at' => $anchor->copy()->setHour(9),
            'ends_at' => $anchor->copy()->setHour(10),
        ]);

        $weekBookings = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->call('jumpToDate', $anchor->toDateString())
            ->set('positionFilter', 'EGLL')
            ->get('weekBookings');

        $rows = $weekBookings[$anchor->toDateString()];
        $this->assertCount(1, $rows);
        $this->assertSame('EGLL_TWR', $rows[0]['position']);
    }

    #[Test]
    public function it_excludes_events_from_week_bookings_when_a_callsign_filter_is_applied(): void
    {
        $anchor = Carbon::parse('2026-09-16');
        $position = Position::factory()->create(['callsign' => 'EGLL_TWR']);

        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => $anchor->copy()->setHour(9),
            'ends_at' => $anchor->copy()->setHour(10),
        ]);
        Event::factory()->published()->create([
            'name' => 'Test event',
            'start' => $anchor->copy()->setHour(18),
            'end' => $anchor->copy()->setHour(20),
        ]);

        $weekBookings = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->call('jumpToDate', $anchor->toDateString())
            ->set('positionFilter', 'EGLL')
            ->get('weekBookings');

        $this->assertCount(1, $weekBookings[$anchor->toDateString()]);
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
        $eventDate = Carbon::today()->addDays(2);

        Event::factory()->published()->create([
            'name' => 'UK Controller Meet',
            'start' => $eventDate->copy()->setHour(19),
            'end' => $eventDate->copy()->setHour(21),
        ]);

        $weekBookings = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->call('jumpToDate', $eventDate->toDateString())
            ->get('weekBookings');

        $dayEntries = $weekBookings[$eventDate->toDateString()];

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
    public function it_centers_on_today_when_switching_to_day_view(): void
    {
        Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->call('jumpToDate', Carbon::today()->addDays(5)->toDateString())
            ->call('setViewMode', 'day')
            ->assertSet('viewMode', 'day')
            ->assertSet('selectedDate', Carbon::today());
    }

    #[Test]
    public function it_switches_to_day_view_for_the_clicked_day_header(): void
    {
        $date = Carbon::today()->addDays(2);

        Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->call('viewDayFromWeek', $date->toDateString())
            ->assertSet('viewMode', 'day')
            ->assertSet('selectedDate', $date->copy()->startOfDay());
    }

    #[Test]
    public function it_renders_each_day_header_as_clickable_to_jump_to_that_day(): void
    {
        Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertSeeHtml("wire:click=\"viewDayFromWeek('".Carbon::today()->toDateString()."')\"");
    }

    // #[Test]
    // public function it_marks_past_day_headers_distinctly_from_today_and_future_days(): void
    // {
    //     Livewire::test(Calendar::class)
    //         ->call('setViewMode', 'week')
    //         ->assertSeeHtml('data-day-state="past"')
    //         ->assertSeeHtml('data-day-state="today"')
    //         ->assertSeeHtml('data-day-state="future"');
    // }

    #[Test]
    public function it_shows_the_day_and_week_toggle_buttons(): void
    {
        Livewire::test(Calendar::class)
            ->assertSee('Day')
            ->assertSee('Week');
    }

    #[Test]
    public function it_hides_day_navigation_but_keeps_position_search_in_week_mode(): void
    {
        Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertDontSee('Previous day')
            ->assertSee('Search callsign...');
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
    public function it_starts_the_week_window_on_the_monday_of_the_selected_dates_week(): void
    {
        $wednesday = Carbon::parse('2026-09-16');
        $this->assertSame(3, $wednesday->dayOfWeekIso, 'sanity check: 2026-09-16 must be a Wednesday');

        $component = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->call('jumpToDate', $wednesday->toDateString());

        $expectedStart = Carbon::parse('2026-09-14');
        $this->assertTrue($expectedStart->isSameDay($component->instance()->weekWindowStart()));
    }

    #[Test]
    public function it_keeps_the_week_window_start_unchanged_for_a_monday_selected_date(): void
    {
        $monday = Carbon::parse('2026-09-14');

        $component = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->call('jumpToDate', $monday->toDateString());

        $this->assertTrue($monday->isSameDay($component->instance()->weekWindowStart()));
    }

    #[Test]
    public function it_shows_the_iso_week_number_in_the_header(): void
    {
        $date = Carbon::parse('2026-09-16');

        Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->call('jumpToDate', $date->toDateString())
            ->assertSee('Week 38');
    }

    #[Test]
    public function it_stays_in_week_mode_after_navigating_with_jump_to_date(): void
    {
        $component = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->call('jumpToDate', Carbon::today()->addDays(10)->toDateString());

        $component->assertSet('viewMode', 'week');
    }

    #[Test]
    public function it_includes_the_raw_booking_on_an_unmerged_block(): void
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

        $this->assertSame((string) $booking->id, $blocks[0]['raw']['id']);
        $this->assertSame('EGKK_APP', $blocks[0]['raw']['position']);
    }

    #[Test]
    public function it_has_no_raw_booking_on_a_merged_block(): void
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

        $this->assertNull($blocks[0]['raw']);
    }

    #[Test]
    public function it_dispatches_the_detail_modal_event_for_an_unmerged_week_block(): void
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(10),
        ]);

        Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertSeeHtml("new CustomEvent('open-detail-modal'");
    }

    #[Test]
    public function it_lists_distinct_position_badge_codes_on_a_merged_block(): void
    {
        $del = Position::factory()->create(['callsign' => 'EGKK_DEL', 'type' => Position::TYPE_DELIVERY]);
        $gnd = Position::factory()->create(['callsign' => 'EGKK_GND', 'type' => Position::TYPE_GROUND]);

        Booking::factory()->create([
            'position_id' => $del->id,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(11),
        ]);
        Booking::factory()->create([
            'position_id' => $gnd->id,
            'starts_at' => Carbon::today()->setHour(10),
            'ends_at' => Carbon::today()->setHour(12),
        ]);

        $blocks = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->instance()
            ->buildWeekDayBlocks(Carbon::today()->toDateString());

        $this->assertSame(['DEL', 'GND'], $blocks[0]['positionCodes']);
    }

    #[Test]
    public function it_deduplicates_position_badge_codes_on_a_merged_block(): void
    {
        $del1 = Position::factory()->create(['callsign' => 'EGKK_DEL', 'type' => Position::TYPE_DELIVERY]);
        $del2 = Position::factory()->create(['callsign' => 'EGKK_B_DEL', 'type' => Position::TYPE_DELIVERY]);

        Booking::factory()->create([
            'position_id' => $del1->id,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(11),
        ]);
        Booking::factory()->create([
            'position_id' => $del2->id,
            'starts_at' => Carbon::today()->setHour(10),
            'ends_at' => Carbon::today()->setHour(12),
        ]);

        $blocks = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->instance()
            ->buildWeekDayBlocks(Carbon::today()->toDateString());

        $this->assertSame(['DEL'], $blocks[0]['positionCodes']);
    }

    #[Test]
    public function it_has_no_badge_codes_for_a_merged_block_of_unbadged_position_types(): void
    {
        $atis1 = Position::factory()->create(['callsign' => 'EGKK_ATIS', 'type' => Position::TYPE_ATIS]);
        $atis2 = Position::factory()->create(['callsign' => 'EGKK_B_ATIS', 'type' => Position::TYPE_ATIS]);

        Booking::factory()->create([
            'position_id' => $atis1->id,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(11),
        ]);
        Booking::factory()->create([
            'position_id' => $atis2->id,
            'starts_at' => Carbon::today()->setHour(10),
            'ends_at' => Carbon::today()->setHour(12),
        ]);

        $blocks = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->instance()
            ->buildWeekDayBlocks(Carbon::today()->toDateString());

        $this->assertSame([], $blocks[0]['positionCodes']);
    }

    #[Test]
    public function it_has_no_badge_codes_for_ctr_and_fss_positions_on_a_merged_block(): void
    {
        $ctr = Position::factory()->create(['callsign' => 'EGTT_CTR', 'type' => Position::TYPE_ENROUTE]);
        $fss = Position::factory()->create(['callsign' => 'EGTT_FSS', 'type' => Position::TYPE_FSS]);

        Booking::factory()->create([
            'position_id' => $ctr->id,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(11),
        ]);
        Booking::factory()->create([
            'position_id' => $fss->id,
            'starts_at' => Carbon::today()->setHour(10),
            'ends_at' => Carbon::today()->setHour(12),
        ]);

        $blocks = Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->instance()
            ->buildWeekDayBlocks(Carbon::today()->toDateString());

        $this->assertSame([], $blocks[0]['positionCodes']);
    }

    #[Test]
    public function it_renders_position_badges_for_a_merged_block(): void
    {
        $del = Position::factory()->create(['callsign' => 'EGKK_DEL', 'type' => Position::TYPE_DELIVERY]);
        $gnd = Position::factory()->create(['callsign' => 'EGKK_GND', 'type' => Position::TYPE_GROUND]);

        Booking::factory()->create([
            'position_id' => $del->id,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(11),
        ]);
        Booking::factory()->create([
            'position_id' => $gnd->id,
            'starts_at' => Carbon::today()->setHour(10),
            'ends_at' => Carbon::today()->setHour(12),
        ]);

        Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertSeeHtml('bg-[#458CFF]')
            ->assertSeeHtml('bg-[#4A9C25]');
    }

    #[Test]
    public function it_shows_the_type_icon_for_a_non_standard_booking_in_the_week_grid(): void
    {
        // Freeze "now" so the 9-10 slot below never counts as an ended session.
        $this->travelTo(Carbon::parse('2026-09-16 08:00:00'));

        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        Booking::factory()->create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_MENTORING,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(10),
        ]);

        Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertSeeHtml('rounded shrink-0 flex items-center justify-center text-white w-4 h-4');
    }

    #[Test]
    public function it_shows_no_extra_type_icon_for_a_standard_booking_in_the_week_grid(): void
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(10),
        ]);

        Livewire::test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertDontSeeHtml('rounded shrink-0 flex items-center justify-center text-white w-4 h-4');
    }

    #[Test]
    public function it_highlights_the_current_members_own_booking_in_the_week_grid(): void
    {
        $member = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        Booking::factory()->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(10),
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertSeeHtml('py-1.5 ring-2 ring-yellow-300 ring-inset');
    }

    #[Test]
    public function it_does_not_highlight_another_members_booking_in_the_week_grid(): void
    {
        $member = Account::factory()->create();
        $other = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP', 'type' => Position::TYPE_APPROACH]);

        Booking::factory()->create([
            'position_id' => $position->id,
            'member_id' => $other->id,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(10),
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertDontSeeHtml('py-1.5 ring-2 ring-yellow-300 ring-inset');
    }

    #[Test]
    public function it_highlights_a_merged_block_containing_the_current_members_booking(): void
    {
        $member = Account::factory()->create();
        $other = Account::factory()->create();
        $del = Position::factory()->create(['callsign' => 'EGLL_DEL', 'type' => Position::TYPE_DELIVERY]);
        $gnd = Position::factory()->create(['callsign' => 'EGLL_GND', 'type' => Position::TYPE_GROUND]);

        Booking::factory()->create([
            'position_id' => $del->id,
            'member_id' => $other->id,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(11),
        ]);
        Booking::factory()->create([
            'position_id' => $gnd->id,
            'member_id' => $member->id,
            'starts_at' => Carbon::today()->setHour(10),
            'ends_at' => Carbon::today()->setHour(12),
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertSeeHtml('py-1.5 ring-2 ring-yellow-300 ring-inset');
    }

    #[Test]
    public function it_does_not_highlight_a_merged_block_without_the_current_members_booking(): void
    {
        $member = Account::factory()->create();
        $other = Account::factory()->create();
        $del = Position::factory()->create(['callsign' => 'EGLL_DEL', 'type' => Position::TYPE_DELIVERY]);
        $gnd = Position::factory()->create(['callsign' => 'EGLL_GND', 'type' => Position::TYPE_GROUND]);

        Booking::factory()->create([
            'position_id' => $del->id,
            'member_id' => $other->id,
            'starts_at' => Carbon::today()->setHour(9),
            'ends_at' => Carbon::today()->setHour(11),
        ]);
        Booking::factory()->create([
            'position_id' => $gnd->id,
            'member_id' => $other->id,
            'starts_at' => Carbon::today()->setHour(10),
            'ends_at' => Carbon::today()->setHour(12),
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('setViewMode', 'week')
            ->assertDontSeeHtml('py-1.5 ring-2 ring-yellow-300 ring-inset');
    }
}
