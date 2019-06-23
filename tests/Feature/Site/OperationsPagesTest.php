<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class OperationsPagesTest extends TestCase
{
    /** @test */
    public function testItLoadsTheLandingPage()
    {
        $this->get(route('site.operations.landing'))->assertOk();
    }

    /** @test */
    public function testItLoadsTheSectorsPage()
    {
        $this->get(route('site.operations.sectors'))->assertOk();
    }
}
