<?php

use Carbon\Carbon;

class SnowTest extends \Tests\TestCase
{
    public function testSnowDuringPeriod()
    {
        \Carbon\Carbon::setTestNow(new Carbon('1st December 2019'));

        $home = $this->get(route('site.home'))
            ->assertOk()
            ->getContent();

        $this->assertTrue((bool) preg_match('/snow-.*\.js/', $home));

        \Carbon\Carbon::setTestNow(new Carbon('5th January 2019'));

        $main = $this->get(route('site.join'))
            ->assertOk()
            ->getContent();

        $this->assertTrue((bool) preg_match('/snow-.*\.js/', $main));
    }

    public function testNoSnowOutsidePeriod()
    {
        \Carbon\Carbon::setTestNow(new Carbon('10th January 2020'));

        $main = $this->get(route('site.home'))
            ->assertOk()
            ->getContent();

        $this->assertFalse((bool) preg_match('/snow-.*\.js/', $main));

        $main = $this->get(route('site.join'))
            ->assertOk()
            ->getContent();

        $this->assertFalse((bool) preg_match('/snow-.*\.js/', $main));
    }
}
