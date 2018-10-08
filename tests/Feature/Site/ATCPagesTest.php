<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class ATCPagesTest extends TestCase
{
    /** @test */
    public function itLoadsTheLandingPage()
    {
        $this->get(route('site.atc.landing'))->assertOk();
    }

    /** @test */
    public function itLoadsTheNewControllerPage()
    {
        $this->get(route('site.atc.newController'))->assertOk();
    }

    /** @test */
    public function itLoadsTheProgressionGuidePage()
    {
        $this->get(route('site.atc.progression'))->assertOk();
    }

    /** @test */
    public function itLoadsTheEndorsementsPage()
    {
        $this->get(route('site.atc.endorsements'))->assertOk();
    }

    /** @test */
    public function itLoadsTheBecomingAMentorPage()
    {
        $this->get(route('site.atc.mentor'))->assertOk();
    }

    /** @test */
    public function itLoadsTheBookingsPage()
    {
        $this->get(route('site.atc.bookings'))->assertOk();
    }
}
