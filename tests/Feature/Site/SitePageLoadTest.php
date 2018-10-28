<?php

namespace Tests\Feature\Site;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SitePageLoadTest extends TestCase
{
    /** @test * */
    public function itLoadsTheJoinUsPage()
    {
        $this->get(route('site.join'))->assertOk();
    }

    /** @test * */
    public function itLoadsTheStaffPageRegardlessOfIPBKey()
    {
        Config::set([
            'ipboard.api_key' => 'Invalid_API_Key',
        ]);

        $this->get(route('site.staff'))->assertOk();
    }

    /** @test **/
    public function testItRetrievesURLFromCache()
    {
        Cache::put(54, 'test.url', 1440);

        $this->get(route('site.staff'))->assertOk();

        Cache::shouldReceive('get')->with(54)->andReturn('test.url');
    }
}
