<?php

namespace Tests\Feature\Account;

use App\Console\Commands\Members\ImportStaleMembers;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Sleep;
use Tests\TestCase;
use Tests\Unit\Training\WaitingList\WaitingListTestHelper;

class StaleMemberTest extends TestCase
{
    use WaitingListTestHelper;

    public function test_ok()
    {
        $this->artisan('import:stale-members')->assertOk();
    }

    public function test_finds_stale_members_and_updates_cert()
    {
        $account = Account::factory()->create([
            'cert_checked_at' => '2000-01-01 00:00:00',
        ]);
        $account->addState(State::findByCode('DIVISION'), 'EUR', 'GBR');
        $waitingList = $this->createList();
        $waitingList->addToWaitingList($account, $this->privacc);
        $this->assertTrue($waitingList->includesAccount($account));

        Http::fake([
            config('services.vatsim-net.api.base')."members/{$account->id}" => Http::response([
                'region_id' => 'EUR', // @fixme our tests / seeding data seem to rely on this being EUR, it is actually EMA
                'division_id' => 'GBR',
            ], 200),
        ]);

        $testNow = now();
        Carbon::setTestNow($testNow);
        $this->artisan('import:stale-members');

        $account->refresh();
        $this->assertEquals($testNow, $account->cert_checked_at);
        $this->assertTrue($account->hasState(State::findByCode('DIVISION')));
        $this->assertTrue($waitingList->includesAccount($account));
    }

    public function test_removes_from_division_and_list()
    {
        $account = Account::factory()->create([
            'cert_checked_at' => '2000-01-01 00:00:00',
        ]);
        $account->addState(State::findByCode('DIVISION'), 'EUR', 'GBR');
        $waitingList = $this->createList();
        $waitingList->addToWaitingList($account, $this->privacc);
        $this->assertTrue($waitingList->includesAccount($account));

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
        $this->artisan('import:stale-members');

        $account->refresh();
        $this->assertFalse($account->hasState(State::findByCode('DIVISION')));
        $this->assertEquals($testNow, $account->cert_checked_at);
        $this->assertFalse($waitingList->includesAccount($account));
    }

    public function test_handles_missing_members()
    {
        $account = Account::factory()->create([
            'cert_checked_at' => '2000-01-01 00:00:00',
        ]);
        $account->addState(State::findByCode('DIVISION'), 'EUR', 'GBR');
        $waitingList = $this->createList();
        $waitingList->addToWaitingList($account, $this->privacc);

        Http::fake([
            config('services.vatsim-net.api.base')."members/{$account->id}" => Http::response([
                'detail' => 'not found',
            ], 404),
        ]);

        $this->artisan('import:stale-members')->assertOk();
        $this->assertFalse($waitingList->includesAccount($account));
    }

    public function test_handles_rate_limit_problems()
    {
        $account = Account::factory()->create([
            'cert_checked_at' => '2000-01-01 00:00:00',
        ]);
        $account->addState(State::findByCode('DIVISION'), 'EUR', 'GBR');
        $waitingList = $this->createList();
        $waitingList->addToWaitingList($account, $this->privacc);

        Http::fake([
            config('services.vatsim-net.api.base')."members/{$account->id}" => Http::sequence()
                ->pushStatus(429)
                ->push([
                    'region_id' => 'EMEA',
                    'division_id' => 'EUD',
                ], 200),
        ]);

        Sleep::fake();
        $this->artisan('import:stale-members')->assertOk();
        Sleep::assertSequence([
            Sleep::for(60)->seconds(),
        ]);
    }

    public function test_rate_limits()
    {
        $waitingList = $this->createList();
        $accounts = Account::factory()->count(10)->create([
            'cert_checked_at' => '2000-01-01 00:00:00',
        ]);
        foreach ($accounts as $account) {
            $account->addState(State::findByCode('DIVISION'), 'EUR', 'GBR');
            $waitingList->addToWaitingList($account, $this->privacc);
        }

        $this->assertCount(10, ImportStaleMembers::findHomeAccountsWithStaleChecks());

        Http::fake([
            config('services.vatsim-net.api.base').'members/*' => Http::response([
                'region_id' => 'EUR', // @fixme our tests / seeding data seem to rely on this being EUR, it is actually EMA
                'division_id' => 'GBR',
            ], 200),
        ]);

        Sleep::fake();
        Sleep::whenFakingSleep(function () {
            RateLimiter::clear(ImportStaleMembers::RATE_LIMIT_KEY);
        });
        $this->artisan('import:stale-members')->assertOk();
        Sleep::assertSequence([
            Sleep::for(60)->seconds(),
        ]);
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

    public function test_ignores_accounts_no_on_list()
    {
        $account = Account::factory()->create([
            'cert_checked_at' => '2000-01-01 00:00:00',
        ]);
        $account->addState(State::findByCode('DIVISION'), 'GBR', 'EUR');

        $ids = ImportStaleMembers::findHomeAccountsWithStaleChecks();
        $this->assertCount(0, $ids);
    }
}
