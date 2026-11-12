<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\DenyIfBanned;
use App\Http\Middleware\MandatoryTwoFactor;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Ban;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LogViewerAccessTest extends TestCase
{
    use DatabaseTransactions;

    private string $path = '/log-viewer';

    #[Test]
    public function it_applies_the_access_checks_to_the_web_and_api_routes()
    {
        foreach ([config('log-viewer.middleware'), config('log-viewer.api_middleware')] as $middleware) {
            $this->assertContains(DenyIfBanned::class, $middleware);
            $this->assertContains(MandatoryTwoFactor::class, $middleware);
        }
    }

    #[Test]
    public function it_redirects_banned_accounts_to_the_ban_page()
    {
        $account = Account::factory()->has(Ban::factory())->create();
        $account->givePermissionTo('log-viewer.access');

        $this->actingAs($account);

        $this->get($this->path)->assertRedirect(route('banned.local'));
    }
}
