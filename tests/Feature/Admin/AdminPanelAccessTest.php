<?php

namespace Tests\Feature\Admin\Filament;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
}
