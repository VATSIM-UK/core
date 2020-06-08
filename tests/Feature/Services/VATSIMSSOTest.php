<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use VatsimSSO;

class VATSIMSSOTest extends TestCase
{
    /** @test */
    public function testItDisplaysWontThrowIfCurlError()
    {
        // Set to non-existent SSO server
        $initialBase = config('vatsim-sso.base');
        config(['vatsim-sso.base' => 'example.org']);

        $this->post(route('login'))
            ->assertRedirect()
            ->assertSessionHasErrors(['connection']);

        config(['vatsim-sso.base' => $initialBase]);
    }

    /** @test */
    public function testItWillRedirectToCorrectSSO()
    {
        $this->post(route('login'))
            ->assertSessionHasNoErrors()
            ->assertRedirect(VatsimSSO::sendToVatsim());
    }
}
