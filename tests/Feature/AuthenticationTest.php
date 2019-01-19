<?php


namespace Tests\Feature;

use VatsimSSO;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /** @test */
    public function testItDisplaysWontThrowIfCurlError()
    {
        $before_val = config('vatsim-sso.base');
        config(['vatsim-sso.base' => 'example.org']);

        $this->post(route('login'))
            ->assertRedirect()
            ->assertSessionHasErrors(['connection']);

        config(['vatsim-sso.base' => $before_val]);
    }

    /** @test */
    public function testItWillRedirectToCorrectSSO()
    {
        $this->post(route('login'))->assertSessionHasNoErrors()->assertRedirect(VatsimSSO::sendToVatsim());
    }
}
