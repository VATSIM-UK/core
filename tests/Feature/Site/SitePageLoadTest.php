<?php

namespace Tests\Feature\Site;

use Alawrence\Ipboard\Ipboard;
use Illuminate\Support\Facades\Config;
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

    /** @test * */
    public function itLoadsTheStaffPageRegardlessOfIPBKey()
    {
        Config::set([
            'ipboard.api_key' => 'Invalid_API_Key',
        ]);

        $this->get(route('site.staff'))->assertOk();
    }
}
