<?php

namespace Tests\Feature\Admin\Filament;

use App\Http\Middleware\AdminPanelFilamentAccessMiddleware;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Ban;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_allows_privacc_to_access_filament()
    {
        $this->actingAs($this->privacc);

        $this->get('/admin')->assertStatus(200);
    }

    #[Test]
    public function it_redirects_to_login_when_unauthenticated()
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    #[Test]
    public function it_returns403_when_navigating_to_url_without_role()
    {
        $account = Account::factory()->create();

        $this->actingAs($account);

        $this->get('/admin')->assertStatus(404);
    }

    #[Test]
    public function it_returns200_when_navigating_to_url_with_role()
    {
        $account = Account::factory()->create();

        $role = factory(Role::class)->create();
        $role->givePermissionTo('admin.access');

        $account->assignRole($role);

        $this->actingAs($account);

        $this->get('/admin')->assertStatus(200);
    }

    #[Test]
    public function it_redirects_banned_accounts_to_the_ban_page()
    {
        $account = Account::factory()->has(Ban::factory())->create();
        $account->givePermissionTo('admin.access');

        $this->actingAs($account);

        $this->get('/admin')->assertRedirect(route('banned.local'));
    }

    #[Test]
    public function it_reapplies_panel_access_middleware_on_livewire_updates()
    {
        $this->assertContains(AdminPanelFilamentAccessMiddleware::class, Livewire::getPersistentMiddleware());
    }
}
