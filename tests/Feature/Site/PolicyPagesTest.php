<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class PolicyPagesTest extends TestCase
{
    /** @test */
    public function test_it_loads_the_division_policy()
    {
        $this->get(route('site.policy.division'))->assertOk();
    }

    /** @test */
    public function test_it_loads_the_atc_training_policy()
    {
        $this->get(route('site.policy.atc-training'))->assertOk();
    }

    /** @test */
    public function test_it_loads_the_visiting_and_transferring_policy()
    {
        $this->get(route('site.policy.visiting-and-transferring'))->assertOk();
    }

    /** @test */
    public function test_it_loads_the_terms()
    {
        $this->get(route('site.policy.terms'))->assertOk();
    }

    /** @test */
    public function test_it_loads_the_privacy_policy()
    {
        $this->get(route('site.policy.privacy'))->assertOk();
    }

    /** @test */
    public function test_it_loads_the_data_protection_policy()
    {
        $this->get(route('site.policy.data-protection'))->assertOk();
    }

    /** @test */
    public function test_it_loads_the_branding_page()
    {
        $this->get(route('site.policy.branding'))->assertOk();
    }

    /** @test */
    public function test_it_loads_the_streaming_page()
    {
        $this->get(route('site.policy.streaming'))->assertOk();
    }
}
