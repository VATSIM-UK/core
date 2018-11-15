<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class OperationsPagesTest extends TestCase
{
    /** @test */
    public function itLoadsTheLandingPage()
    {
        $this->get(route('site.operations.landing'))->assertOk();
    }

    /** @test */
    public function itLoadsTheSectorsPage()
    {
        $this->get(route('site.operations.sectors'))->assertOk();
    }
}
