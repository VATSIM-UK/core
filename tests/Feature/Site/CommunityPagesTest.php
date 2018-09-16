<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class CommunityPagesTest extends TestCase
{
    /** @test */
    public function itLoadsTheVtGuide()
    {
        $this->get(route('site.community.vt-guide'))->assertOk();
    }

    /** @test */
    public function itLoadTheTerms()
    {
        $this->get(route('site.community.terms'))->assertOk();
    }

    /** @test */
    public function itLoadsTeamSpeak()
    {
        $this->get(route('site.community.teamspeak'))->assertOk();
    }
}
