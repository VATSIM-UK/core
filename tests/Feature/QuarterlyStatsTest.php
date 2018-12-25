<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

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
