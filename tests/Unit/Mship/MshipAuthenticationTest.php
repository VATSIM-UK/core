<?php

namespace Tests\Unit\Mship;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class MshipAuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Instance used for the tests
     *
     * @var \App\Http\Controllers\Auth\LoginController
     */
    protected $authenticationInstance;

    /** @var Account $account */
    protected $account;

    /**
     * Nothing fancy here, just create an instance of the class at the
     * the start of the test so we don't have to create a new one for every
     * single test.
     */
    public function setUp()
    {
        parent::setUp();
        $this->authenticationInstance = new \App\Http\Controllers\Auth\LoginController();
        $this->account = factory(Account::class)->create([
            'name_first' => 'John',
            'name_last' => 'Doe',
            'email' => 'no-reply@vatsim.uk',
        ]);
        Session::flush();
    }

    /**
     * Set the instance to null so that we definitely get a new one for
     * the next test.
     *
     * This also resets Session:: for us after each test.
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->authenticationInstance = null;
    }

    /**
     * Exactly what it says on the tin...
     *
     * @test
     **/
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
