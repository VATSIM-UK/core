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
    public function test_it_loads_the_sectors_page()
    {
        $this->get(route('site.operations.sectors'))->assertOk();
    }
}
