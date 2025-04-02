<?php

namespace Tests\Feature\Site;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function test_it_loads_the_homepage()
    {
        $this->get(route('site.home'))->assertOk();
    }
}
