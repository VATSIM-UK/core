<?php

namespace Tests\Unit\Account\Sync;

use App\Events\Mship\AccountAltered;
use App\Jobs\Mship\SyncToCTS;
use App\Jobs\Mship\SyncToHelpdesk;
use App\Jobs\Mship\SyncToMoodle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AccountAlteredEventTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush(); // Remove time lockout cache
    }

    /** @test */
    public function itTriggersEvent()
    {
        Event::fake();
        event(new AccountAltered($this->user));
        Event::assertDispatched(AccountAltered::class);
    }

    /** @test */
    public function itTriggersJobs()
    {
        Queue::fake();
        event(new AccountAltered($this->user));

        Queue::assertPushed(SyncToCTS::class);
        Queue::assertPushed(SyncToMoodle::class);
        Queue::assertPushed(SyncToHelpdesk::class);
        //Queue::assertPushed(SyncToForums::class);
    }

    /** @test */
    public function itTriggersJobsOnlyOnce()
    {
        Queue::fake();

        event(new AccountAltered($this->user));
        event(new AccountAltered($this->user));

        Queue::assertPushed(SyncToCTS::class, 1);
        Queue::assertPushed(SyncToMoodle::class, 1);
        Queue::assertPushed(SyncToHelpdesk::class, 1);
        //Queue::assertPushed(SyncToForums::class, 1);
    }

    /** @test */
    public function itWontTriggerWithSemiDefinedAccounts()
    {
        Queue::fake();

        $this->user->email = null;
        Cache::flush(); // Remove time lockout cache
        event(new AccountAltered($this->user));

        Queue::assertNotPushed(SyncToCTS::class);
        Queue::assertNotPushed(SyncToMoodle::class);
        Queue::assertNotPushed(SyncToHelpdesk::class);
        //Queue::assertNotPushed(SyncToForums::class);
    }
}
