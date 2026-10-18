<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Models\Mship\Account;
use App\Support\MemberDisplayName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberDisplayNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_abbreviates_to_preferred_first_name_and_last_initial(): void
    {
        $account = Account::factory()->create(['name_first' => 'Alexander', 'name_last' => 'Smith']);

        $this->assertSame('Alexander S.', MemberDisplayName::abbreviated($account));
    }

    public function test_it_prefers_the_nickname_over_the_first_name(): void
    {
        $account = Account::factory()->create(['name_first' => 'Alexander', 'name_last' => 'Smith', 'nickname' => 'Alex']);

        $this->assertSame('Alex S.', MemberDisplayName::abbreviated($account));
    }

    public function test_it_appends_the_cid(): void
    {
        $account = Account::factory()->create(['id' => 1234567, 'name_first' => 'Alex', 'name_last' => 'Smith']);

        $this->assertSame('Alex S. (1234567)', MemberDisplayName::abbreviatedWithCid($account));
    }
}
