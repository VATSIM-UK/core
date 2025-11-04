<?php

namespace Tests\Feature\Site;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PolicyPagesTest extends TestCase
{
    #[Test]
    public function test_it_loads_the_division_policy()
    {
        $this->get(route('site.policy.division'))->assertOk();
    }

    #[Test]
    public function test_it_loads_the_atc_training_policy()
    {
        $this->get(route('site.policy.atc-training'))->assertOk();
    }

    #[Test]
    public function test_it_loads_the_visiting_and_transferring_policy()
    {
        $this->get(route('site.policy.visiting-and-transferring'))->assertOk();
    }

    #[Test]
    public function test_it_loads_the_terms()
    {
        $this->get(route('site.policy.terms'))->assertOk();
    }

    #[Test]
    public function test_it_loads_the_privacy_policy()
    {
        $this->get(route('site.policy.privacy'))->assertOk();
    }

    #[Test]
    public function test_it_loads_the_data_protection_policy()
    {
        $this->get(route('site.policy.data-protection'))->assertOk();
    }

    #[Test]
    public function test_it_loads_the_branding_page()
    {
        $this->get(route('site.policy.branding'))->assertOk();
    }

    #[Test]
    public function test_it_loads_the_streaming_page()
    {
        $this->get(route('site.policy.streaming'))->assertOk();
    }

    #[Test]
    public function test_it_loads_the_s1_syllabus()
    {
        $this->get(route('site.policy.training.s1-syllabus'))->assertOk();
    }

    #[Test]
    public function test_it_loads_the_s3_syllabus()
    {
        $this->get(route('site.policy.training.s3-syllabus'))->assertOk();
    }
}
