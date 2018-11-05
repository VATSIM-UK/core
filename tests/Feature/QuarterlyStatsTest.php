<?php

namespace Tests\Feature;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuarterlyStatsTest extends TestCase
{
    use DatabaseTransactions;

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
