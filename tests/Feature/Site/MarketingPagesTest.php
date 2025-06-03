<?php

namespace Tests\Feature\Site;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MarketingPagesTest extends TestCase
{
    #[Test]
    public function test_it_loads_the_branding_page()
    {
        $this->get(route('site.marketing.branding'))->assertOk();
    }
}
