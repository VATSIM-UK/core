<?php

namespace Tests\Feature\Account;

use App\Console\Commands\Members\ImportStaleMembers;
use App\Jobs\Mship\ScanStaleMembers;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use Tests\TestCase;
use Tests\Unit\Training\WaitingList\WaitingListTestHelper;

class StaleMemberTest extends TestCase
{
    use WaitingListTestHelper;

    public function test_ok()
    {
        \Queue::fake();
        $this->artisan('import:stale-members')->assertOk();
    }

    public function test_finds_stale_members_and_dispatches_job()
    {
        \Queue::fake();

        $account = Account::factory()->create([
            'cert_checked_at' => '2000-01-01 00:00:00',
        ]);
        $account->addState(State::findByCode('DIVISION'), 'EUR', 'GBR');
        $waitingList = $this->createList();
        $waitingList->addToWaitingList($account, $this->privacc);
        $this->assertTrue($waitingList->includesAccount($account));

        $this->artisan('import:stale-members')->assertOk();
        \Queue::assertPushed(function (ScanStaleMembers $job) use ($account) {
            return $job->accountIds->contains($account->id) && $job->accountIds->count() === 1;
        });
    }

    public function test_ignores_accounts_no_on_list()
    {
        $account = Account::factory()->create([
            'cert_checked_at' => '2000-01-01 00:00:00',
        ]);
        $account->addState(State::findByCode('DIVISION'), 'GBR', 'EUR');

        $ids = ImportStaleMembers::findHomeAccountsWithStaleChecks();
        $this->assertCount(0, $ids);
    }

    public function test_ignores_international_members()
    {
        $account = Account::factory()->create([
            'cert_checked_at' => '2000-01-01 00:00:00',
        ]);
        $account->addState(State::findByCode('DIVISION'), 'GBR', 'EUR');

        $internationalAccount = Account::factory()->create([
            'cert_checked_at' => '2000-01-01 00:00:00',
        ]);
        $internationalAccount->addState(State::findByCode('INTERNATIONAL'));

        $waitingList = $this->createList();
        $waitingList->addToWaitingList($account, $this->privacc);
        $waitingList->addToWaitingList($internationalAccount, $this->privacc);

        $ids = ImportStaleMembers::findHomeAccountsWithStaleChecks();

        $this->assertCount(1, $ids);
        $this->assertContains($account->id, $ids);
        $this->assertNotContains($internationalAccount->id, $ids);
    }
}
