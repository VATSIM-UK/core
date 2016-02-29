<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class MshipTest extends TestCase
{
    use DatabaseTransactions;

    /** @test **/
    public function it_redirects_to_the_landing_page_when_viewing_the_root_url()
    {
        $this->visit("/");

        $this->assertRedirectedToRoute("mship.manage.landing");

    }
}