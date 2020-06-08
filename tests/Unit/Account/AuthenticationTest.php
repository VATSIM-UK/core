<?php

namespace Tests\Unit\Account;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Instance used for the tests.
     *
     * @var \App\Http\Controllers\Auth\LoginController
     */
    protected $authenticationInstance;

    public function setUp(): void
    {
        parent::setUp();
        $this->authenticationInstance = new \App\Http\Controllers\Auth\LoginController();
        Session::flush();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->authenticationInstance = null;
    }

    /** @test */
    public function itConstructs()
    {
        $this->assertInstanceOf(\App\Http\Controllers\Auth\LoginController::class, $this->authenticationInstance);
    }

    /** @test */
    public function itRedirectsToLoginIfNoAuth()
    {
        // In this test, we assert that we're getting a redirect to the login page if no auth at all
        $expectedStatus = 302;
        $expectedRedirectRegExp = '#\h*<title>Redirecting to '.route('dashboard').'<\/title>\h*#';
        $expectedObjectType = 'Illuminate\Http\RedirectResponse';

        $result = $this->authenticationInstance->getLogin();

        $this->assertInstanceOf($expectedObjectType, $result);
        $this->assertEquals($expectedStatus, $result->status());
        $this->assertRegExp($expectedRedirectRegExp, $result->content());
    }
}
