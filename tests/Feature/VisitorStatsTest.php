<?php

namespace Tests\Feature;

use App\Models\Mship\Account;
use App\Models\Mship\Role;
use App\Models\Mship\State;
use Carbon\Carbon;
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

    /** @test **/
    public function testOnlyVisitingControllersAreSelected()
    {
        $this->actingAs($this->account)->get(route('visiting.admin.hours.index'))
            ->assertViewIs('visit-transfer.admin.hours.list')
            ->assertViewHas(['accounts', 'startDate', 'endDate']);
    }
}
