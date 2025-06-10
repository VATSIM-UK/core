<?php

namespace Tests\Feature\Site;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SitePageLoadTest extends TestCase
{
    #[Test]
    public function test_it_loads_the_join_us_page()
    {
        $this->get(route('site.join'))->assertOk();
    }

    #[Test]
    public function test_it_retrieves_url_from_cache()
    {
        Cache::put(54, 'test.url', 1440 * 60);

        $this->get(route('site.staff'))->assertOk();

        Cache::shouldReceive('get')->with(54)->andReturn('test.url');
    }
}
