<?php

namespace Tests\Feature;

use App\Models\Mship\Account;
use App\Models\Mship\State;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VisitorStatsTest extends TestCase
{
    use DatabaseTransactions;

    private $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = factory(Account::class)->create();

        $this->account->assignRole(Role::findById(1));

        $this->account->addState(State::findByCode('DIVISION'));
    }

    /** @test * */
    public function testNoHoursArePulledOnGet()
    {
        $this->actingAs($this->account)->get(route('adm.visiting.hours.create'))
            ->assertDontSee('accounts');
    }

    /** @test * */
    public function testOnlyVisitingControllersAreSelected()
    {
        $this->actingAs($this->account)->get(route('adm.visiting.hours.search'))
            ->assertSee('accounts')
            ->assertSee('startDate')
            ->assertSee('endDate');
    }
}
