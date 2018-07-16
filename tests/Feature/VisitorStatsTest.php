<?php

namespace Tests\Feature;

use App\Models\Mship\Account;
use App\Models\Mship\Role;
use App\Models\Mship\State;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        $startDate = Carbon::parse('first day of this month');

        $endDate = Carbon::parse('last day of this month');

        $accounts = Account::with(['networkDataAtc' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('disconnected_at', [$startDate, $endDate]);
        }, 'qualifications', 'states'])
        ->whereHas('states', function ($query) {
            $query->where('code', '=', 'VISITING');
        })->orderBy('id', 'asc')->paginate(25);

        $this->actingAs($this->account)->get(route('visiting.admin.hours.index'))
            ->assertViewIs('visit-transfer.admin.hours.list');
    }
}
