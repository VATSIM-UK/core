<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class SitePageLoadTest extends TestCase
{
    /** @test * */
    public function itLoadsTheHomepage()
    {
        $this->get(route('site.home'))->assertOk();
    }

    /** @test * */
    public function itLoadsTheJoinUsPage()
    {
        $this->get(route('site.join'))->assertOk();
    }
}
