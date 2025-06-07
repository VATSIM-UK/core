<?php

namespace Tests\Feature\Site;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function test_it_loads_the_homepage()
    {
        $this->get(route('site.home'))->assertOk();
    }
}
