<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands\Members;

use App\Enums\BanTypeEnum;
use App\Jobs\UpdateMember;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Ban;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncExpiredBansCommandTest extends TestCase
{
    use DatabaseTransactions;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        $this->account = Account::factory()->create();
        Ban::query()->delete();
    }

    private function createBan(array $attributes): Ban
    {
        return Ban::factory()->create(array_merge([
            'account_id' => $this->account->id,
            'banned_by' => null,
            'type' => BanTypeEnum::Local,
            'reason_id' => null,
            'repealed_at' => null,
        ], $attributes));
    }

    #[Test]
    public function it_dispatches_sync_jobs_for_accounts_with_recently_expired_bans(): void
    {
        $this->createBan([
            'period_start' => now()->subHours(6),
            'period_finish' => now()->subHours(2),
        ]);

        Artisan::call('mship:sync-expired-bans');
        Bus::assertDispatched(UpdateMember::class, fn ($job) => $job->accountID === $this->account->id);
    }

    #[Test]
    public function it_does_not_dispatch_for_bans_outside_the_lookback_window(): void
    {
        $this->createBan([
            'period_start' => now()->subDays(2),
            'period_finish' => now()->subDays(1),
        ]);

        Artisan::call('mship:sync-expired-bans');
        Bus::assertNotDispatched(UpdateMember::class);
    }

    #[Test]
    public function it_does_not_dispatch_for_still_active_bans(): void
    {
        $this->createBan([
            'period_start' => now()->subHours(2),
            'period_finish' => now()->addHours(2),
        ]);

        Artisan::call('mship:sync-expired-bans');
        Bus::assertNotDispatched(UpdateMember::class);
    }
}
