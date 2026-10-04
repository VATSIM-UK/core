<?php

declare(strict_types=1);

namespace Tests\Unit\Discord;

use App\Models\Mship\Account;
use App\Services\Discord\CidLookupService;
use App\Services\Discord\CidLookupStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CidLookupServiceTest extends TestCase
{
    #[Test]
    public function it_returns_invalid_for_a_non_positive_cid()
    {
        $result = (new CidLookupService)->lookup(0);

        $this->assertSame(CidLookupStatus::Invalid, $result->status);
        $this->assertNull($result->discordId);
    }

    #[Test]
    public function it_returns_not_found_when_no_account_has_that_cid()
    {
        // AccountFactory only generates ids in 100_000..9_999_999, so this is guaranteed unused.
        $result = (new CidLookupService)->lookup(999999999);

        $this->assertSame(CidLookupStatus::NotFound, $result->status);
        $this->assertNull($result->discordId);
    }

    #[Test]
    public function it_returns_not_linked_when_account_has_no_discord_id()
    {
        $account = Account::factory()->createQuietly(['discord_id' => null]);

        $result = (new CidLookupService)->lookup($account->id);

        $this->assertSame(CidLookupStatus::NotLinked, $result->status);
        $this->assertNull($result->discordId);
    }

    #[Test]
    public function it_returns_found_with_discord_id_when_linked()
    {
        $account = Account::factory()->createQuietly(['discord_id' => 555444333]);

        $result = (new CidLookupService)->lookup($account->id);

        $this->assertSame(CidLookupStatus::Found, $result->status);
        $this->assertSame('555444333', $result->discordId);
    }
}
