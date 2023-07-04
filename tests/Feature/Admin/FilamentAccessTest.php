<?php

namespace Tests\Feature\Admin\Filament;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilamentAccessTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itAllowsPrivaccToAccessFilament()
    {
        $this->actingAs($this->privacc);

        $this->get('/filament')->assertStatus(200);
    }

    /** @test */
    public function itReturns403WhenNavigatingToUrlWithoutRole()
    {
        $account = Account::factory()->create();

        $this->actingAs($account);

        $this->get('/filament')->assertStatus(403);
    }

    /** @test */
    public function itReturns200WhenNavigatingToUrlWithRole()
    {
        $account = Account::factory()->create();

        $role = factory(Role::class)->create();
        $role->givePermissionTo('admin.access');

        $account->assignRole($role);

        $this->actingAs($account);

        $this->get('/filament')->assertStatus(200);
    }
}
