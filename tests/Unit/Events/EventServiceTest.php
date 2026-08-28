<?php

namespace Tests\Unit\Events;

use App\Models\Events\Event;
use App\Services\Events\EventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_publish_sets_published_at_and_returns_incomplete_flags(): void
    {
        $event = Event::factory()->create([
            'published_at' => null,
            'eoi_published' => true,
            'roster_published' => false,
            'banner_created' => false,
        ]);

        $incomplete = app(EventService::class)->publish($event);

        $this->assertNotNull($event->fresh()->published_at);
        $this->assertContains('Roster published', $incomplete);
        $this->assertContains('Banner created', $incomplete);
    }

    public function test_publish_does_not_block_on_incomplete_flags(): void
    {
        $event = Event::factory()->create(['published_at' => null]);

        app(EventService::class)->publish($event);

        $this->assertNotNull($event->fresh()->published_at);
    }
}
