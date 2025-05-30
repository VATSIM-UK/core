<?php

namespace Tests\Feature\Account;

use App\Console\Commands\Members\ImportStaleMembers;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StaleMemberTest extends TestCase
{
    public function test_ok()
    {
        $this->artisan('import:stale-members')->assertOk();
    }

    public function test_finds_stale_members()
    {
        $account = Account::factory()->create([
            'cert_checked_at' => '2000-01-01 00:00:00',
        ]);
        $account->addState(State::findByCode('DIVISION'));

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
    }

    public function test_removes_from_division()
    {
        $account = Account::factory()->create([
            'cert_checked_at' => '2000-01-01 00:00:00',
        ]);
        $account->addState(State::findByCode('DIVISION'));
        $account->save();
        $account->refresh();

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
    }

    public function test_handles_missing_members()
    {
        $account = Account::factory()->create([
            'cert_checked_at' => '2000-01-01 00:00:00',
        ]);

        Http::fake([
            config('services.vatsim-net.api.base')."members/{$account->id}" => Http::response([
                'detail' => 'not found',
            ], 404),
        ]);

        $this->artisan('import:stale-members')->assertOk();
    }

    public function test_ignores_international_members()
    {
        $account = Account::factory()->create([
            'cert_checked_at' => '2000-01-01 00:00:00',
        ]);
        $account->addState(State::findByCode('DIVISION'));

        $internationalAccount = Account::factory()->create([
            'cert_checked_at' => '2000-01-01 00:00:00',
        ]);
        $internationalAccount->addState(State::findByCode('INTERNATIONAL'));

        $ids = ImportStaleMembers::getAccountIds();

        $this->assertCount(1, $ids);
        $this->assertContains($account->id, $ids);
        $this->assertNotContains($internationalAccount->id, $ids);
    }
}
