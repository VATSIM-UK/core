<?php

namespace Tests\Feature;

use App\Models\Mship\Account;
use App\Models\Mship\Role;
use App\Models\Mship\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorStatsTest extends TestCase
{
    use RefreshDatabase;

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
        $this->actingAs($this->account)->get(route('visiting.admin.hours.create'))
            ->assertViewIs('visit-transfer.admin.hours.index')
            ->assertViewMissing('accounts');
    }

    /** @test * */
    public function testOnlyVisitingControllersAreSelected()
    {
        $this->actingAs($this->account)->get(route('visiting.admin.hours.search'))
            ->assertViewIs('visit-transfer.admin.hours.list')
            ->assertViewHas(['accounts', 'startDate', 'endDate']);
    }
}
