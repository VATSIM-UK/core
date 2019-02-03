<?php

namespace Tests\Unit\Mship\Sync;

use App\Events\Mship\AccountAltered;
use App\Jobs\Mship\SyncToCTS;
use App\Jobs\Mship\SyncToForums;
use App\Jobs\Mship\SyncToHelpdesk;
use App\Jobs\Mship\SyncToMoodle;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AccountAlteredTest extends TestCase
{
    use DatabaseTransactions;

    /** @test * */
    public function itTriggersEvent()
    {
        Event::fake();

        $account = factory(Account::class)->create();
        event(new AccountAltered($account));

        Event::assertDispatched(AccountAltered::class);
    }

    /** @test * */
    public function itTriggersJobs()
    {
        Queue::fake();

        $account = factory(Account::class)->create();
        event(new AccountAltered($account));

        Queue::assertPushed(SyncToCTS::class);
        Queue::assertPushed(SyncToMoodle::class);
        Queue::assertPushed(SyncToHelpdesk::class);
        //Queue::assertPushed(SyncToForums::class);
    }

    /** @test * */
    public function itTriggersJobsOnlyOnce()
    {
        Queue::fake();

        $account = factory(Account::class)->create();
        event(new AccountAltered($account));
        event(new AccountAltered($account));

        Queue::assertPushed(SyncToCTS::class, 1);
        Queue::assertPushed(SyncToMoodle::class, 1);
        Queue::assertPushed(SyncToHelpdesk::class, 1);
        //Queue::assertPushed(SyncToForums::class, 1);
    }

    /** @test * */
    public function itWontTriggerWithSemiDefinedAccounts()
    {
        Queue::fake();

        $account = factory(Account::class)->create(['email' => null]);
        event(new AccountAltered($account));

        Queue::assertNotPushed(SyncToCTS::class);
        Queue::assertNotPushed(SyncToMoodle::class);
        Queue::assertNotPushed(SyncToHelpdesk::class);
        //Queue::assertNotPushed(SyncToForums::class);
    }
}
