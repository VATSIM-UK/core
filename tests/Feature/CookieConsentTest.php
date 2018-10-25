<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    use RefreshDatabase;

    /** @test * */
    public function testGuestDoesNotHaveCookieSet()
    {
        $this->get(route('site.home'))->assertCookieMissing('vuk_cookie_consent')
            ->assertSee('Your experience on this site will be improved by allowing cookies.');
    }
}
