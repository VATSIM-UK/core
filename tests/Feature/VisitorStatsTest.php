<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Mship\Account;
use Spatie\Permission\Models\Role;

class VisitorStatsTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();

        $privaccHolder = factory(Account::class)->create();
        $privaccHolder->assignRole(Role::findByName('privacc'));
        $this->privacc = $privaccHolder->fresh();
    }

    /** @test * */
    public function testNoHoursArePulledOnGet()
    {
        $this->actingAs($this->privacc)
            ->get(route('adm.visiting.hours.create'))
            ->assertDontSee('accounts');
    }

    /** @test * */
    public function testOnlyVisitingControllersAreSelected()
    {
        $this->withoutMiddleware('auth_full_group')
            ->actingAs($this->privacc)
            ->get(route('adm.visiting.hours.search'))
            ->assertSee('accounts')
            ->assertSee('startDate')
            ->assertSee('endDate');
    }
}
