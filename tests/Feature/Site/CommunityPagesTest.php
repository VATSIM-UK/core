<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class CommunityPagesTest extends TestCase
{
    /** @test */
    public function testItLoadsTheVtGuide()
    {
        $this->get(route('site.community.vt-guide'))->assertOk();
    }

    /** @test */
    public function testItLoadTheTerms()
    {
        $this->get(route('site.community.terms'))->assertOk();
    }

    /** @test */
    public function testItLoadsTeamSpeak()
    {
        $this->get(route('site.community.teamspeak'))->assertOk();
    }
}
