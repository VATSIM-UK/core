<?php

declare(strict_types=1);

namespace Tests\Unit\Observers;

use App\Jobs\Discord\SyncDiscordTags;
use App\Models\Discord\DiscordTag;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DiscordTagObserverTest extends TestCase
{
    #[Test]
    public function it_dispatches_sync_job_when_tag_is_created()
    {
        Bus::fake();

        DiscordTag::factory()->create();

        Bus::assertDispatched(SyncDiscordTags::class);
    }

    #[Test]
    public function it_dispatches_sync_job_when_tag_is_updated()
    {
        Bus::fake();

        $tag = DiscordTag::factory()->create();

        Bus::assertDispatched(SyncDiscordTags::class);

        Bus::fake();

        $tag->update(['value' => 'updated content']);

        Bus::assertDispatched(SyncDiscordTags::class);
    }

    #[Test]
    public function it_dispatches_sync_job_when_tag_is_deleted()
    {
        Bus::fake();

        $tag = DiscordTag::factory()->create();

        Bus::assertDispatched(SyncDiscordTags::class);

        Bus::fake();

        $tag->delete();

        Bus::assertDispatched(SyncDiscordTags::class);
    }
}
