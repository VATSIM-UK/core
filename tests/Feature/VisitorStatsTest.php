<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class VisitorStatsTest extends TestCase
{
    use DatabaseTransactions;

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
