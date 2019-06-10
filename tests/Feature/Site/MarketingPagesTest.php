<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class MarketingPagesTest extends TestCase
{
    /** @test */
    public function testItLoadsTheLiveStreamingPage()
    {
        $this->get(route('site.marketing.live-streaming'))->assertOk();
    }

    /** @test */
    public function testItLoadsTheBrandingPage()
    {
        $this->get(route('site.marketing.branding'))->assertOk();
    }
}
