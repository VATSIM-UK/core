<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class PilotPagesTest extends TestCase
{
    /** @test */
    public function test_it_loads_the_landing_page()
    {
        $this->get(route('site.pilots.landing'))->assertOk();
    }

    /** @test */
    public function test_it_loads_the_ratings_page()
    {
        $this->get(route('site.pilots.ratings'))->assertOk();
    }

    /** @test */
    public function test_it_loads_the_mentor_page()
    {
        $this->get(route('site.pilots.mentor'))->assertOk();
    }

    /** @test */
    public function test_it_loads_the_oceanic_page()
    {
        $this->get(route('site.pilots.oceanic'))->assertOk();
    }

    /** @test */
    public function test_it_loads_the_stand_guide_page()
    {
        $this->get(route('site.pilots.stands'))->assertOk();
    }
}
