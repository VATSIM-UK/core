<?php

namespace Tests\Feature\Adm;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;

    public function setUp(): void
    {
        parent::setUp();

        $admin = factory(Account::class)->create();
        $admin->assignRole('privacc');
        $this->admin = $admin->fresh();
    }

    /** @test **/
    public function it_redirects_to_dashboard_when_loading_root()
    {
        $this->actingAs($this->admin)
                ->get('/adm')
                ->assertRedirect(route('adm.dashboard'));
    }
}
