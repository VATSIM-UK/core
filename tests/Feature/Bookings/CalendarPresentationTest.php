<?php

declare(strict_types=1);

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\Calendar;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Cts\Event;
use App\Models\Cts\Member as CtsMember;
use App\Models\Mship\Account;
use App\Repositories\Cts\BookingRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

/**
 * Row order on the timeline, and the icons and legend that let the booking types
 * be told apart without relying on colour.
 */
class CalendarPresentationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Literal template markup, not booking data: public properties are serialised
     * into the root element's wire:snapshot ahead of the body, so a callsign or
     * event name appears twice and cannot be used to compare positions.
     */
    private const EVENTS_ROW_MARKER = 'Events</span>';

    private const POSITION_ROW_MARKER = ':title="pos.callsign"';

    /** Enough to put one position row on the timeline; the type is irrelevant. */
    private function bookOnePosition(Carbon $date, string $callsign = 'EGKK_APP'): void
    {
        $member = Account::factory()->create();
        $ctsMember = CtsMember::factory()->forAccount($member)->create();

        CtsBooking::factory()->create([
            'position' => $callsign,
            'member_id' => $ctsMember->id,
            'type' => 'BK',
            'date' => $date->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);
    }

    /**
     * The rows only. The detail modal repeats the block icons, so a whole-page
     * assertion would pass on its copy alone and miss the blocks losing theirs.
     */
    private function timelineRegion(string $html): string
    {
        $footer = strpos($html, 'Drag across an empty slot');
        $this->assertNotFalse($footer, 'The footer hint marks the end of the timeline region');

        return substr($html, 0, $footer);
    }

    private function footerRegion(string $html): string
    {
        $footer = strpos($html, 'Drag across an empty slot');
        $modal = strpos($html, 'Booking Details');

        $this->assertNotFalse($footer, 'The footer must be rendered');
        $this->assertNotFalse($modal, 'The detail modal marks the end of the footer region');

        return substr($html, $footer, $modal - $footer);
    }

    private function detailModalRegion(string $html): string
    {
        $heading = strpos($html, 'Booking Details');
        $this->assertNotFalse($heading, 'The detail modal must be rendered');

        return substr($html, $heading);
    }

    private function addEvent(Carbon $date, string $name = 'Cross the Pond'): void
    {
        Event::factory()->create([
            'event' => $name,
            'date' => $date->toDateString(),
            'from' => '18:00:00',
            'to' => '22:00:00',
            'gone' => 0,
        ]);
    }

    #[Test]
    public function it_renders_the_events_row_before_any_position_row(): void
    {
        $date = Carbon::today();
        $this->addEvent($date);
        $this->bookOnePosition($date);

        $html = Livewire::test(Calendar::class)->html();

        $eventsRow = strpos($html, self::EVENTS_ROW_MARKER);
        $positionRow = strpos($html, self::POSITION_ROW_MARKER);

        $this->assertNotFalse($eventsRow, 'The events row must be rendered');
        $this->assertNotFalse($positionRow, 'A position row must be rendered');
        $this->assertLessThan(
            $positionRow,
            $eventsRow,
            'The events row must come first: an event applies division-wide and has to be read before the position rows under it'
        );
    }

    #[Test]
    public function the_events_row_stays_first_when_the_day_is_full_of_positions(): void
    {
        $date = Carbon::today();
        $this->addEvent($date);

        // Spans both the grouped (ICAO) and single-callsign halves of the timeline.
        foreach (['EGKK_APP', 'EGKK_TWR', 'EGLL_APP', 'EGLL_TWR', 'EGPH_APP', 'LON_CTR', 'SCO_CTR'] as $callsign) {
            $this->bookOnePosition($date, $callsign);
        }

        $html = Livewire::test(Calendar::class)->html();

        $this->assertLessThan(
            strpos($html, self::POSITION_ROW_MARKER),
            strpos($html, self::EVENTS_ROW_MARKER),
            'The events row must precede the first position row regardless of how many there are'
        );
    }

    #[Test]
    public function it_renders_no_events_row_when_the_day_has_no_events(): void
    {
        $date = Carbon::today();
        $this->bookOnePosition($date);

        $html = Livewire::test(Calendar::class)->html();

        $this->assertStringNotContainsString(
            self::EVENTS_ROW_MARKER,
            $html,
            'Moving the events row to the top must not make it render on days with no events'
        );
        $this->assertStringContainsString(self::POSITION_ROW_MARKER, $html);
    }

    #[Test]
    public function it_still_shows_the_empty_state_when_there_is_nothing_at_all(): void
    {
        $html = Livewire::test(Calendar::class, ['year' => 2026, 'month' => 1])->html();

        $this->assertStringContainsString('No positions available for this date.', $html);
        $this->assertStringNotContainsString(self::EVENTS_ROW_MARKER, $html);
    }

    #[Test]
    public function booking_blocks_carry_a_type_icon_for_every_type_colour_cannot_separate(): void
    {
        $date = Carbon::today();
        $this->bookOnePosition($date);

        $timeline = $this->timelineRegion(Livewire::test(Calendar::class)->html());

        foreach (['ME', 'EX', 'GS'] as $type) {
            $this->assertStringContainsString(
                sprintf('x-if="booking.type === \'%s\'"', $type),
                $timeline,
                sprintf('Booking blocks must carry an icon for the %s type, not colour alone', $type)
            );
        }
    }

    #[Test]
    public function booking_blocks_state_their_type_for_assistive_technology(): void
    {
        $date = Carbon::today();
        $this->bookOnePosition($date);

        $timeline = $this->timelineRegion(Livewire::test(Calendar::class)->html());

        $this->assertStringContainsString(
            '<span class="sr-only" x-text="$bookingTypeLabel(booking.type)">',
            $timeline,
            'A block whose type is shown only as an icon must still name that type for a screen reader'
        );
        $this->assertStringContainsString(':title="$bookingTypeLabel(booking.type)', $timeline);
    }

    #[Test]
    public function the_footer_legend_names_every_booking_type(): void
    {
        $date = Carbon::today();
        $this->bookOnePosition($date);

        $footer = $this->footerRegion(Livewire::test(Calendar::class)->html());

        foreach (Calendar::TYPE_LEGEND as $code => $legend) {
            $this->assertStringContainsString(
                '>'.$legend['label'].'</span>',
                $footer,
                sprintf('The legend must name the %s type', $code)
            );
            $this->assertStringContainsString(
                $legend['colour'],
                $footer,
                sprintf('The legend must show the swatch colour used for %s bookings', $code)
            );
        }
    }

    #[Test]
    public function the_legend_covers_every_type_the_repository_can_produce(): void
    {
        // Read rather than restated, so adding a type there and forgetting the
        // legend fails here instead of shipping an unexplained colour.
        $typeMap = (new ReflectionClass(BookingRepository::class))->getConstant('TYPE_MAP');

        $this->assertNotFalse($typeMap, 'BookingRepository::TYPE_MAP has been renamed or removed');

        $missing = array_diff(array_values($typeMap), array_keys(Calendar::TYPE_LEGEND));

        $this->assertSame(
            [],
            array_values($missing),
            'Every booking type the calendar can show must have a legend entry: '.implode(', ', $missing)
        );
    }

    #[Test]
    public function every_legend_icon_resolves_to_a_real_heroicon(): void
    {
        foreach (Calendar::TYPE_LEGEND as $code => $legend) {
            if ($legend['icon'] === null) {
                continue;
            }

            // svg() throws on an unknown icon, so a typo fails here, not as a 500.
            $this->assertStringContainsString(
                '<svg',
                svg($legend['icon'])->toHtml(),
                sprintf('The %s legend icon "%s" must exist in the heroicons set', $code, $legend['icon'])
            );
        }
    }

    #[Test]
    public function the_frontend_type_labels_match_the_legend(): void
    {
        // $bookingTypeLabel is defined in JavaScript, where no PHP test reaches it.
        $source = file_get_contents(resource_path('assets/js/bookings-calendar.js'));

        $this->assertMatchesRegularExpression(
            '/const BOOKING_TYPE_LABELS = \{(.+?)\};/s',
            $source,
            'BOOKING_TYPE_LABELS has been renamed or removed from bookings-calendar.js'
        );

        preg_match('/const BOOKING_TYPE_LABELS = \{(.+?)\};/s', $source, $matches);
        preg_match_all('/^\s*([A-Z]{2}):/m', $matches[1], $codes);

        $labelled = $codes[1];
        $legend = array_keys(Calendar::TYPE_LEGEND);
        sort($labelled);
        sort($legend);

        // Order is not compared: it carries no meaning in either place.
        $this->assertSame(
            $legend,
            $labelled,
            'BOOKING_TYPE_LABELS must cover the same booking types as Calendar::TYPE_LEGEND'
        );
    }

    #[Test]
    public function the_detail_modal_names_the_type_it_was_opened_for(): void
    {
        $modal = $this->detailModalRegion(Livewire::test(Calendar::class)->html());

        $this->assertStringContainsString('<span x-text="$bookingTypeLabel(booking.type)">', $modal);

        foreach (['ME', 'EX', 'GS'] as $type) {
            $this->assertStringContainsString(
                sprintf('x-if="booking.type === \'%s\'"', $type),
                $modal,
                sprintf('The detail modal must repeat the %s icon from the block that was clicked', $type)
            );
        }
    }
}
