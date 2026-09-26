<?php

declare(strict_types=1);

namespace Tests\Feature\Events;

use App\Livewire\Events\Show;
use App\Models\Events\Event;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PublicEventShowTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_is_publicly_accessible_for_a_published_event(): void
    {
        $event = Event::factory()->published()->create(['name' => 'Lorem Ipsum Dolor']);

        $this->get(route('site.events.show', $event))
            ->assertOk()
            ->assertSee('Lorem Ipsum Dolor');
    }

    #[Test]
    public function it_returns_not_found_for_a_draft_event(): void
    {
        $event = Event::factory()->create(['published_at' => null]);

        $this->get(route('site.events.show', $event))->assertNotFound();
    }

    #[Test]
    public function it_shows_the_date_in_the_admin_format(): void
    {
        $event = Event::factory()->published()->create([
            'start' => Carbon::parse('2026-10-18 18:00:00', 'UTC'),
            'end' => Carbon::parse('2026-10-18 22:00:00', 'UTC'),
        ]);

        $this->get(route('site.events.show', $event))
            ->assertOk()
            ->assertSee('18. 10. 2026')
            ->assertSee('18:00');
    }

    #[Test]
    public function it_abbreviates_organisers_and_includes_their_cid(): void
    {
        $manager = Account::factory()->create(['id' => 1234567, 'name_first' => 'Alex', 'name_last' => 'Smith']);
        $event = Event::factory()->published()->withManagers($manager)->create();

        $this->get(route('site.events.show', $event))
            ->assertOk()
            ->assertSee('Alex S. (1234567)');
    }

    #[Test]
    public function it_links_to_the_bookings_calendar_when_the_event_is_not_rostered(): void
    {
        $event = Event::factory()->published()->create([
            'rostered' => false,
            'start' => Carbon::parse('2026-10-18 18:00:00', 'UTC'),
        ]);

        $this->get(route('site.events.show', $event))
            ->assertOk()
            ->assertSee('Book a position')
            ->assertSee(route('site.bookings.calendar', ['year' => 2026, 'month' => 10]));
    }

    #[Test]
    public function it_does_not_link_to_the_bookings_calendar_when_rostered(): void
    {
        $event = Event::factory()->published()->create(['rostered' => true]);

        $this->get(route('site.events.show', $event))
            ->assertOk()
            ->assertDontSee('Book a position');
    }

    #[Test]
    public function it_downloads_an_ics_file(): void
    {
        $event = Event::factory()->published()->create(['name' => 'Lorem Ipsum Dolor']);

        Livewire::test(Show::class, ['event' => $event])
            ->call('downloadIcs')
            ->assertFileDownloaded();
    }

    #[Test]
    public function it_offers_calendar_links(): void
    {
        $event = Event::factory()->published()->create();

        Livewire::test(Show::class, ['event' => $event])
            ->assertSee('Google Calendar')
            ->assertSee('Add to calendar');
    }
}
