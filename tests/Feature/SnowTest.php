<?php

use Carbon\Carbon;

class SnowTest extends \Tests\TestCase
{
    private $script;

    public function setUp(): void
    {
        parent::setUp();
        $this->script = '<script src="'.mix('js/snow.js').'"></script>';
    }

    public function testSnowDuringPeriod()
    {
        \Carbon\Carbon::setTestNow(new Carbon('1st December 2019'));
        $this->get(route('site.home'))
            ->assertOk()
            ->assertSee($this->script, false);
        $this->get(route('site.join'))->assertOk()
            ->assertSee($this->script, false);

        \Carbon\Carbon::setTestNow(new Carbon('5th January 2019'));
        $this->get(route('site.home'))
            ->assertOk()
            ->assertSee($this->script, false);
    }

    public function testNoSnowOutsidePeriod()
    {
        \Carbon\Carbon::setTestNow(new Carbon('10th January 2020'));
        $this->get(route('site.home'))
            ->assertOk()
            ->assertDontSee($this->script, false);
        $this->get(route('site.join'))
            ->assertOk()
            ->assertDontSee($this->script, false);
    }
}
