<?php

namespace Tests\Feature\Site;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function testUserPromtedToAcceptCookies()
    {
        $this->get(route('site.home'))
            ->assertCookieMissing('vuk_cookie_consent')
            ->assertSee('Your experience on this site will be improved by allowing cookies.');
    }
}
