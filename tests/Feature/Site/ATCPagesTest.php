<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class ATCPagesTest extends TestCase
{
    /** @test */
    public function testItLoadsTheLandingPage()
    {
        $this->get(route('site.atc.landing'))->assertOk();
    }

    /** @test */
    public function testItLoadsTheNewControllerPage()
    {
        $this->get(route('site.atc.newController'))->assertOk();
    }

    /** @test */
    public function testItLoadsTheEndorsementsPage()
    {
        $this->get(route('site.atc.endorsements'))->assertOk();
    }

    /** @test */
    public function testItLoadsTheHeathrowPage()
    {
        $this->get(route('site.atc.heathrow'))->assertOk();
    }

    /** @test */
    public function testItLoadsTheBecomingAMentorPage()
    {
        $this->get(route('site.atc.mentor'))->assertOk();
    }

    /** @test */
    public function testItLoadsTheBookingsPage()
    {
        $this->get(route('site.atc.bookings'))->assertOk();
    }
}
