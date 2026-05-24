<?php

namespace Tests\Unit\Roster;

use App\Jobs\Mship\SyncToDiscord;
use App\Models\Mship\Account;
use App\Models\Roster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RosterObserverTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.discord.token' => null]);
    }

    #[Test]
    public function it_dispatches_sync_to_discord_when_roster_is_created(): void
    {
        Queue::fake();

        $account = Account::factory()->create();
        $account->discord_id = 1234;
        $account->saveQuietly();

        Roster::create(['account_id' => $account->id]);

        Queue::assertPushed(SyncToDiscord::class, 1);
    }

    #[Test]
    public function it_does_not_dispatch_sync_when_account_has_no_discord(): void
    {
        Queue::fake();

        $account = Account::factory()->create();

        Roster::create(['account_id' => $account->id]);

        Queue::assertNotPushed(SyncToDiscord::class);
    }

    #[Test]
    public function it_dispatches_sync_to_discord_when_roster_is_deleted(): void
    {
        $account = Account::factory()->create();
        $account->discord_id = 1234;
        $account->saveQuietly();

        $roster = new Roster(['account_id' => $account->id]);
        $roster->saveQuietly();

        Queue::fake();

        $roster->delete();

        Queue::assertPushed(SyncToDiscord::class, 1);
    }
}
