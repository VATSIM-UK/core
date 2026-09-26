<?php

declare(strict_types=1);

namespace Tests\Feature\Events;

use App\Models\Events\Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PublicEventsIndexTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_is_publicly_accessible(): void
    {
        $this->get(route('site.events.index'))->assertOk();
    }

    #[Test]
    public function it_lists_published_upcoming_events(): void
    {
        Event::factory()->published()->create([
            'name' => 'Lorem Ipsum Dolor',
            'start' => now()->addDays(2),
            'end' => now()->addDays(2)->addHours(4),
        ]);

        $this->get(route('site.events.index'))
            ->assertOk()
            ->assertSee('Lorem Ipsum Dolor');
    }

    #[Test]
    public function it_does_not_list_draft_events(): void
    {
        Event::factory()->create([
            'name' => 'Tempor Incididunt Draft',
            'published_at' => null,
            'start' => now()->addDay(),
            'end' => now()->addDay()->addHours(2),
        ]);

        $this->get(route('site.events.index'))
            ->assertOk()
            ->assertDontSee('Tempor Incididunt Draft');
    }

    #[Test]
    public function it_shows_past_events_in_the_archive(): void
    {
        Event::factory()->published()->create([
            'name' => 'Sed Do Eiusmod',
            'start' => now()->subDays(5),
            'end' => now()->subDays(5)->addHours(2),
        ]);

        $this->get(route('site.events.index'))
            ->assertOk()
            ->assertSee('Past events')
            ->assertSee('Sed Do Eiusmod');
    }

    #[Test]
    public function it_marks_rostered_events_and_bookable_events_differently(): void
    {
        Event::factory()->published()->create(['name' => 'Rostered Event', 'rostered' => true, 'start' => now()->addDay(), 'end' => now()->addDay()->addHours(2)]);
        Event::factory()->published()->create(['name' => 'Open Event', 'rostered' => false, 'start' => now()->addDays(2), 'end' => now()->addDays(2)->addHours(2)]);

        $this->get(route('site.events.index'))
            ->assertOk()
            ->assertSee('Rostered')
            ->assertSee('Bookable');
    }

    #[Test]
    public function it_paginates_past_events(): void
    {
        foreach (range(1, 13) as $i) {
            Event::factory()->published()->create([
                'name' => "Past Event {$i}",
                'start' => now()->subDays($i + 1),
                'end' => now()->subDays($i),
            ]);
        }

        $response = $this->get(route('site.events.index'));

        $response->assertOk();

        $this->assertSame(12, substr_count($response->getContent(), 'View event'));
        $response->assertSee('Go to page 2');
    }
}
