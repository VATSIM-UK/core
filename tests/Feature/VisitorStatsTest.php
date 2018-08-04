<?php

namespace Tests\Feature;

use App\Models\Mship\Account;
use App\Models\Mship\Role;
use App\Models\Mship\State;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VisitorStatsTest extends TestCase
{
    use DatabaseTransactions;

    private $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = factory(Account::class)->create();

        $this->account->roles()->attach(Role::find(1));

        $this->account->addState(State::findByCode('DIVISION'));
    }

    /** @test * */
    public function testNoHoursArePulledOnGet()
    {
        $this->actingAs($this->account)->get(route('adm.visiting.hours.create'))
            ->assertViewIs('visit-transfer.admin.hours.index')
            ->assertViewMissing('accounts');
    }

    /** @test * */
    public function testOnlyVisitingControllersAreSelected()
    {
        $this->actingAs($this->account)->get(route('adm.visiting.hours.search'))
            ->assertViewIs('visit-transfer.admin.hours.list')
            ->assertViewHas(['accounts', 'startDate', 'endDate']);
    }
}
