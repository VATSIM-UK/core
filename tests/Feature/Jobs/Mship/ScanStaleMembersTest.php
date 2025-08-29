<?php

namespace Feature\Jobs\Mship;

use App\Jobs\Mship\ScanStaleMembers;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Training\WaitingList;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;
use Tests\Unit\Training\WaitingList\WaitingListTestHelper;

class ScanStaleMembersTest extends TestCase
{
    use WaitingListTestHelper;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::resetAttempts(ScanStaleMembers::RATE_LIMIT_KEY);
    }

    public function test_empty_set()
    {
        \Queue::fake();

        $job = (new ScanStaleMembers(new Collection))->withFakeQueueInteractions();
        $job->handle();
        $job->assertDeleted();

        \Queue::assertNothingPushed();
    }

    public function test_updates_cert_time()
    {
        \Queue::fake();

        [$account, $waitingList] = $this->createAccountOnWaitingList();

        Http::fake([
            config('services.vatsim-net.api.base')."members/{$account->id}" => Http::response([
                'region_id' => 'EUR', // @fixme our tests / seeding data seem to rely on this being EUR, it is actually EMA
                'division_id' => 'GBR',
            ], 200),
        ]);
        $testNow = now();
        Carbon::setTestNow($testNow);

        $job = (new ScanStaleMembers(new Collection([$account->id])))->withFakeQueueInteractions();
        $job->handle();
        $job->assertDeleted();

        $account->refresh();
        $this->assertEquals($testNow, $account->cert_checked_at);
        $this->assertTrue($account->hasState(State::findByCode('DIVISION')));
        $this->assertTrue($waitingList->includesAccount($account));
    }

    public function test_removes_leavers()
    {
        \Queue::fake();

        [$account, $waitingList] = $this->createAccountOnWaitingList();

        // Currently a member
        $this->assertTrue($account->hasState(State::findByCode('DIVISION')));

        // API will report they've moved to germany
        Http::fake([
            config('services.vatsim-net.api.base')."members/{$account->id}" => Http::response([
                'region_id' => 'EMEA',
                'division_id' => 'EUD',
            ], 200),
        ]);

        $testNow = now();
        Carbon::setTestNow($testNow);

        $job = (new ScanStaleMembers(new Collection([$account->id])))->withFakeQueueInteractions();
        $job->handle();
        $job->assertDeleted();

        $account->refresh();
        $this->assertFalse($account->hasState(State::findByCode('DIVISION')));
        $this->assertEquals($testNow, $account->cert_checked_at);
        $this->assertFalse($waitingList->includesAccount($account));
    }

    public function test_handles_members_missing_from_api()
    {
        \Queue::fake();

        [$account, $waitingList] = $this->createAccountOnWaitingList();

        Http::fake([
            config('services.vatsim-net.api.base')."members/{$account->id}" => Http::response([
                'detail' => 'not found',
            ], 404),
        ]);

        $job = (new ScanStaleMembers(new Collection([$account->id])))->withFakeQueueInteractions();
        $job->handle();
        $job->assertDeleted();

        $this->assertFalse($waitingList->includesAccount($account));
    }

    /**
     * Need to make sure that when we push more than the batch size we end up with more jobs
     */
    public function test_dispatches_rest_later()
    {
        $this->assertSame(5, ScanStaleMembers::BATCH_SIZE, 'this test needs updating if the batch size changes');

        \Queue::fake();

        $accountIds = new Collection;

        for ($i = 0; $i < 10; $i++) {
            [$account, $_] = $this->createAccountOnWaitingList();
            $accountIds->push($account->id);
        }

        $this->assertCount(10, $accountIds);

        // all still members
        Http::fake([
            config('services.vatsim-net.api.base').'members/*' => Http::response([
                'region_id' => 'EUR', // @fixme our tests / seeding data seem to rely on this being EUR, it is actually EMA
                'division_id' => 'GBR',
            ], 200),
        ]);

        $testNow = now();
        Carbon::setTestNow($testNow);

        $job = (new ScanStaleMembers(clone $accountIds))->withFakeQueueInteractions();
        $job->handle();
        $job->assertDeleted();

        // check we've seen 5 people updated (the batch size)
        $updateCount = 0;
        $nonUpdatedAccounts = new Collection;
        foreach ($accountIds as $accountId) {
            $account = Account::findOrFail($accountId);
            $account->refresh();
            if ($account->cert_checked_at == $testNow) {
                $updateCount++;
            } else {
                $nonUpdatedAccounts->push($accountId);
            }
        }

        $this->assertEquals(5, $updateCount, 'expected batch size (5) accounts to be updated');
        $this->assertCount(5, $nonUpdatedAccounts, 'expected 5 accounts to not be updated');

        // check the remaining users have gone into a new job
        \Queue::assertPushed(function (ScanStaleMembers $job) use ($nonUpdatedAccounts) {
            return
                $job->accountIds->toArray() == $nonUpdatedAccounts->toArray() &&
                $job->delay->getTimestamp() > 0;
        });
    }

    public function test_redispatches_on_rate_limiting()
    {
        \Queue::fake();

        [$account, $_] = $this->createAccountOnWaitingList();

        // Currently a member
        $this->assertTrue($account->hasState(State::findByCode('DIVISION')));

        // API will report they've moved to germany
        Http::fake([
            config('services.vatsim-net.api.base')."members/{$account->id}" => Http::response([
                'region_id' => 'EMEA',
                'division_id' => 'EUD',
            ], 429),
        ]);

        $testNow = now();
        Carbon::setTestNow($testNow);

        $job = (new ScanStaleMembers(new Collection([$account->id])))->withFakeQueueInteractions();
        $job->handle();
        $job->assertDeleted();

        // check the users has gone into a new job
        \Queue::assertPushed(function (ScanStaleMembers $job) use ($account) {
            return
                $job->accountIds->toArray() == [$account->id] &&
                $job->delay->getTimestamp() > 0;
        });
    }

    public function test_redispatches_on_internal_rate_limiting()
    {
        \Queue::fake();

        [$account, $_] = $this->createAccountOnWaitingList();

        // max out our requests
        RateLimiter::increment(ScanStaleMembers::RATE_LIMIT_KEY, amount: ScanStaleMembers::API_LIMIT_PER_MINUTE);

        $job = (new ScanStaleMembers(new Collection([$account->id])))->withFakeQueueInteractions();
        $job->handle();
        $job->assertDeleted();

        // check the user has gone into a new job
        \Queue::assertPushed(function (ScanStaleMembers $job) use ($account) {
            return
                $job->accountIds->toArray() == [$account->id] &&
                $job->delay->getTimestamp() > 0;
        });
    }

    /**
     * @return list{Account, WaitingList}
     */
    private function createAccountOnWaitingList(): array
    {
        $account = Account::factory()->create([
            'cert_checked_at' => '2000-01-01 00:00:00',
        ]);
        $account->addState(State::findByCode('DIVISION'), 'EUR', 'GBR');
        $waitingList = $this->createList();
        $waitingList->addToWaitingList($account, $this->privacc);
        $this->assertTrue($waitingList->includesAccount($account));

        return [$account, $waitingList];
    }
}
