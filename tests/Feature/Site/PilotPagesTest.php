<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class PilotPagesTest extends TestCase
{
    /** @test */
    public function itLoadsTheLandingPage()
    {
        $this->get(route('site.pilots.landing'))->assertOk();
    }

    /** @test */
    public function itLoadsTheRatingsPage()
    {
        $this->get(route('site.pilots.ratings'))->assertOk();
    }

    /** @test */
    public function itLoadsTheMentorPage()
    {
        $this->get(route('site.pilots.mentor'))->assertOk();
    }

    /** @test */
    public function itLoadsTheOceanicPage()
    {
        $this->get(route('site.pilots.oceanic'))->assertOk();
    }
}
