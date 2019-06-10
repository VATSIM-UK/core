<?php

namespace Tests\Feature\Adm;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function testItRedirectsToDashboardWhenLoadingRoot()
    {
        $this->actingAs($this->privacc)
            ->get('/adm')
            ->assertRedirect(route('adm.dashboard'));
    }
}
