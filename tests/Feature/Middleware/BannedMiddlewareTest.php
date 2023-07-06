<?php

namespace Tests\Feature\Middleware;

use App\Models\Mship\Account;
use App\Models\Mship\Ban\Reason;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BannedMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function testNetworkBannedUserIsRedirectedToCorrectRoute()
    {
        $account = Account::factory()->create();

        $account->addNetworkBan();

        $account->refresh();

        $this->actingAs($account)
            ->get(route('mship.manage.dashboard'))
            ->assertRedirect(route('banned.network'));
    }

    /** @test */
    public function testLocalBannedUserIsRedirectedToCorrectRoute()
    {
        $account = Account::factory()->create();
        $banReason = Reason::factory()->create();

        $account->addBan($banReason, 'Local ban', 'Ban note.');

        $this->actingAs($account)
            ->get(route('mship.manage.dashboard'))
            ->assertRedirect(route('banned.local'));
    }
}
