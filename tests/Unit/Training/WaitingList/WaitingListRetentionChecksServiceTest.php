<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Training\WaitingList\WaitingListRetentionChecks;
use App\Services\Training\WaitingListRetentionChecks as WaitingListRetentionChecksService;
use Tests\TestCase;

class WaitingListRetentionChecksServiceTest extends TestCase
{
    #[Test]
    public function test_fetches_checks_which_have_passed_expiry()
    {
        $expiredChecks = WaitingListRetentionChecks::factory()->create(['expires_at' => now()->subDays(1)]);
        $nonExpiredChecks = WaitingListRetentionChecks::factory()->count(3)->create(['expires_at' => now()->addDays(1)]);

        $queriedExpiredChecks = WaitingListRetentionChecksService::getExpiredChecks(now());

        $this->assertTrue($queriedExpiredChecks->contains($expiredChecks));
    }
}
