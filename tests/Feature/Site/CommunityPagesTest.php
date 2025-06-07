<?php

namespace Tests\Feature\Site;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommunityPagesTest extends TestCase
{
    #[Test]
    public function test_it_loads_the_vt_guide()
    {
        $this->get(route('site.community.vt-guide'))->assertOk();
    }

    #[Test]
    public function test_it_loads_team_speak()
    {
        $this->get(route('site.community.teamspeak'))->assertOk();
    }
}
