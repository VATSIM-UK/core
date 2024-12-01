<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class OperationsPagesTest extends TestCase
{
    /** @test */
    public function test_it_loads_the_landing_page()
    {
        $this->get(route('site.operations.landing'))->assertOk();
    }

    /** @test */
    public function test_it_loads_the_sectors_page()
    {
        $this->get(route('site.operations.sectors'))->assertOk();
    }
}
