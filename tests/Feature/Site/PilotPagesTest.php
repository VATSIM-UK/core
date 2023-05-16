<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class PilotPagesTest extends TestCase
{
    /** @test */
    public function testItLoadsTheLandingPage()
    {
        $this->get(route('site.pilots.landing'))->assertOk();
    }

    /** @test */
    public function testItLoadsTheRatingsPage()
    {
        $this->get(route('site.pilots.ratings'))->assertOk();
    }

    /** @test */
    public function testItLoadsTheMentorPage()
    {
        $this->get(route('site.pilots.mentor'))->assertOk();
    }

    /** @test */
    public function testItLoadsTheOceanicPage()
    {
        $this->get(route('site.pilots.oceanic'))->assertOk();
    }

        /** @test */
    public function testItLoadsTheStandGuidePage()
    {
        $this->get(route('site.pilots.stands'))->assertOk();
    }
}
