<?php

use Carbon\Carbon;

class SnowTest extends \Tests\TestCase {

    private $script;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->script = '<script src="'.mix('js/snow.js').'"></script>';
    }

    public function testSnowDuringPeriod()
    {
        \Carbon\Carbon::setTestNow(new Carbon('1st December 2019'));
        $this->get(route('home'))
            ->assertOk()
            ->assertSee($this->script);
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($this->script);

        \Carbon\Carbon::setTestNow(new Carbon('5th January 2019'));
        $this->get(route('home'))
            ->assertOk()
            ->assertSee($this->script);
    }

    public function testNoSnowOutsidePeriod()
    {
        \Carbon\Carbon::setTestNow(new Carbon('10th January 2020'));
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee($this->script);
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee($this->script);
    }

}
