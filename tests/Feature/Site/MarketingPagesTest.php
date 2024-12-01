<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class MarketingPagesTest extends TestCase
{
    /** @test */
    public function test_it_loads_the_branding_page()
    {
        $this->get(route('site.marketing.branding'))->assertOk();
    }
}
