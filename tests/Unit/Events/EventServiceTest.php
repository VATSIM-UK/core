<?php

namespace Tests\Unit\Events;

use App\Enums\EventChecklistItem;
use App\Models\Events\Event;
use App\Models\Mship\Account;
use App\Services\Events\EventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventServiceTest extends TestCase
{
    use RefreshDatabase;

    private EventService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(EventService::class);
    }

    public function test_publish_sets_published_at_and_returns_incomplete_items(): void
    {
        $publisher = Account::factory()->create();
        $event = Event::factory()
            ->withChecklistItem(EventChecklistItem::EoiPublished)
            ->create(['published_at' => null]);

        $incomplete = $this->service->publish($event, $publisher);

        $this->assertNotNull($event->fresh()->published_at);
        $this->assertNotContains('EOI published', $incomplete);
        $this->assertContains('Roster published', $incomplete);
        $this->assertContains('Banner created', $incomplete);
    }

    public function test_publish_records_the_publishing_account(): void
    {
        $publisher = Account::factory()->create();
        $event = Event::factory()->create(['published_at' => null]);

        $this->service->publish($event, $publisher);

        $this->assertEquals($publisher->id, $event->fresh()->published_by);
    }

    public function test_republishing_moves_the_published_timestamp_forward(): void
    {
        $original = now()->subDays(2);
        $event = Event::factory()->create(['published_at' => $original]);
        $republisher = Account::factory()->create();

        $this->service->publish($event, $republisher);

        $fresh = $event->fresh();
        $this->assertTrue($fresh->published_at->greaterThan($original));
        $this->assertEquals($republisher->id, $fresh->published_by);
    }

    public function test_publish_does_not_block_on_incomplete_items(): void
    {
        $event = Event::factory()->create(['published_at' => null]);

        $this->service->publish($event, Account::factory()->create());

        $this->assertNotNull($event->fresh()->published_at);
    }

    public function test_sync_checklist_records_who_ticked_each_item(): void
    {
        $account = Account::factory()->create();
        $event = Event::factory()->create();

        $this->service->syncChecklist($event, ['eoi_published', 'banner_created'], $account);

        $this->assertDatabaseHas('event_checklist_completions', [
            'event_id' => $event->id,
            'item' => 'eoi_published',
            'account_id' => $account->id,
        ]);
        $this->assertDatabaseHas('event_checklist_completions', [
            'event_id' => $event->id,
            'item' => 'banner_created',
            'account_id' => $account->id,
        ]);
    }

    public function test_sync_checklist_removes_unticked_items(): void
    {
        $account = Account::factory()->create();
        $event = Event::factory()
            ->withChecklistItem(EventChecklistItem::EoiPublished, $account)
            ->withChecklistItem(EventChecklistItem::BannerCreated, $account)
            ->create();

        $this->service->syncChecklist($event, ['eoi_published'], $account);

        $this->assertDatabaseMissing('event_checklist_completions', [
            'event_id' => $event->id,
            'item' => 'banner_created',
        ]);
        $this->assertEquals(['eoi_published'], $event->fresh()->completedChecklistItems());
    }

    public function test_sync_checklist_leaves_untouched_items_attributed_to_the_original_account(): void
    {
        $first = Account::factory()->create();
        $second = Account::factory()->create();
        $event = Event::factory()
            ->withChecklistItem(EventChecklistItem::EoiPublished, $first)
            ->create();

        $this->service->syncChecklist($event, ['eoi_published', 'banner_created'], $second);

        $event = $event->fresh();
        $this->assertEquals($first->id, $event->completionFor(EventChecklistItem::EoiPublished)->account_id);
        $this->assertEquals($second->id, $event->completionFor(EventChecklistItem::BannerCreated)->account_id);
    }

    public function test_sync_checklist_ignores_unknown_items(): void
    {
        $event = Event::factory()->create();

        $this->service->syncChecklist($event, ['not_a_real_item'], Account::factory()->create());

        $this->assertEmpty($event->fresh()->completedChecklistItems());
    }
}
