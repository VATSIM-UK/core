<?php

namespace Tests\Unit\Events;

use App\Enums\EventChecklistItem;
use App\Models\Atc\Position;
use App\Models\Events\Event;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_defaults_to_draft_when_unpublished(): void
    {
        $event = Event::factory()->create(['published_at' => null]);

        $this->assertTrue($event->isDraft());
        $this->assertFalse($event->isPublished());
    }

    public function test_is_published_when_published_at_set(): void
    {
        $event = Event::factory()->create(['published_at' => now()]);

        $this->assertFalse($event->isDraft());
        $this->assertTrue($event->isPublished());
    }

    public function test_published_scope_only_returns_published_events(): void
    {
        Event::factory()->create(['published_at' => now()]);
        Event::factory()->create(['published_at' => null]);

        $this->assertCount(1, Event::published()->get());
    }

    public function test_upcoming_scope_excludes_ended_events(): void
    {
        Event::factory()->create(['start' => now()->subDay(), 'end' => now()->subHour()]);
        Event::factory()->create(['start' => now()->addDay(), 'end' => now()->addDays(2)]);

        $this->assertCount(1, Event::upcoming()->get());
    }

    public function test_unpublished_checklist_lists_only_outstanding_items(): void
    {
        $event = Event::factory()
            ->withChecklistItem(EventChecklistItem::EoiPublished)
            ->withChecklistItem(EventChecklistItem::BriefingPublished)
            ->withChecklistItem(EventChecklistItem::BriefingCreated)
            ->withChecklistItem(EventChecklistItem::EcfmpSetUp)
            ->create();

        $this->assertEquals(
            ['Roster published', 'Banner created', 'My.vatsim.net published'],
            $event->unpublishedChecklist()
        );
    }

    public function test_unpublished_checklist_lists_everything_when_nothing_ticked(): void
    {
        $event = Event::factory()->create();

        $this->assertCount(count(EventChecklistItem::cases()), $event->unpublishedChecklist());
    }

    public function test_completed_checklist_items_returns_ticked_values(): void
    {
        $event = Event::factory()
            ->withChecklistItem(EventChecklistItem::BannerCreated)
            ->create();

        $this->assertEquals(['banner_created'], $event->completedChecklistItems());
        $this->assertTrue($event->hasCompleted(EventChecklistItem::BannerCreated));
        $this->assertFalse($event->hasCompleted(EventChecklistItem::EoiPublished));
    }

    public function test_completion_for_exposes_the_account_that_ticked_it(): void
    {
        $account = Account::factory()->create();
        $event = Event::factory()
            ->withChecklistItem(EventChecklistItem::RosterPublished, $account)
            ->create();

        $completion = $event->completionFor(EventChecklistItem::RosterPublished);

        $this->assertNotNull($completion);
        $this->assertEquals($account->id, $completion->account->id);
        $this->assertNotNull($completion->completed_at);
    }

    public function test_publisher_relation(): void
    {
        $publisher = Account::factory()->create();
        $event = Event::factory()->published($publisher)->create();

        $this->assertEquals($publisher->id, $event->publisher->id);
    }

    public function test_positions_relation(): void
    {
        $position = Position::factory()->create();
        $event = Event::factory()->create();
        $event->positions()->attach($position);

        $this->assertTrue($event->positions->contains($position));
    }

    public function test_manager_relation(): void
    {
        $manager = Account::factory()->create();
        $event = Event::factory()->create(['manager_id' => $manager->id]);

        $this->assertEquals($manager->id, $event->manager->id);
    }
}
