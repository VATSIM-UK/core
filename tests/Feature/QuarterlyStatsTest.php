<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Mship\Account;
use Spatie\Permission\Models\Role;

class QuarterlyStatsTest extends TestCase
{
    use DatabaseTransactions;

    private $privacc;

    public function setUp()
    {
        parent::setUp();

        $privaccHolder = factory(Account::class)->create();
        $privaccHolder->assignRole(Role::findByName('privacc'));
        $this->privacc = $privaccHolder->fresh();
    }

    public function testItLoadsQStats()
    {
        $this->actingAs($this->privacc)
                ->get(route('adm.ops.qstats.index'))
                ->assertSuccessful();
    }

    public function testItGeneratesQStats()
    {
        $stats = [
            'quarter' => '01-01',
            'year' => '2016',
        ];

        $this->actingAs($this->privacc)
                ->post(route('adm.ops.qstats.generate', $stats))
                ->assertSuccessful();
    }
}
