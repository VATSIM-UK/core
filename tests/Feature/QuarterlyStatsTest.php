<?php

namespace Tests\Feature;

use App\Models\Mship\Account;
use App\Models\Mship\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QuarterlyStatsTest extends TestCase
{
    use DatabaseTransactions;

    private $admin;

    public function setUp()
    {
        parent::setUp();

        $this->admin = factory(Account::class)->create();
        $this->admin->roles()->attach(Role::find(1));
    }

    public function testItLoadsQStats()
    {
        $this->actingAs($this->admin, 'web')->get(route('adm.ops.qstats.index'))->assertSuccessful();
    }

    public function testItGeneratesQStats()
    {
        $stats = [
            'quarter' => '01-01',
            'year' => '2016',
        ];

        $this->actingAs($this->admin, 'web')->post(route('adm.ops.qstats.generate', $stats))->assertSuccessful();
    }
}
