<?php

namespace Tests\Feature\Site;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OperationsPagesTest extends TestCase
{
    #[Test]
    public function test_it_loads_the_landing_page()
    {
        $this->get(route('site.operations.landing'))->assertOk();
    }

    #[Test]
    public function test_it_redirects_the_sectors_page()
    {
        $this->get(route('site.operations.sectors'))
            ->assertStatus(301)
            ->assertRedirect('https://docs.vatsim.uk/Pilots/General/Who-to-Call/');
    }
}
