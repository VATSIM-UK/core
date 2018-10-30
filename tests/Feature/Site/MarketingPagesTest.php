<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class MarketingPagesTest extends TestCase
{
    /** @test */
    public function itLoadsTheLiveStreamingPage()
    {
        $this->get(route('site.marketing.live-streaming'))->assertOk();
    }

    /** @test */
    public function itLoadsTheBrandingPage()
    {
        $this->get(route('site.marketing.branding'))->assertOk();
    }
}
