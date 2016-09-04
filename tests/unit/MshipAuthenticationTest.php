<?php

use App\Models\Mship\Account;
use Illuminate\Support\Facades\Session;
class MshipAuthenticationTest extends TestCase
{
    /**
     * Instance used for the tests
     *
     * @var \App\Http\Controllers\Mship\Authentication
     */
    protected $authenticationInstance;

    /**
     * Nothing fancy here, just create an instance of the class at the
     * the start of the test so we don't have to create a new one for every
     * single test.
     */
    public function setUp()
    {
        parent::setUp();
        $this->authenticationInstance = new \App\Http\Controllers\Mship\Authentication();
    }

    /**
     * Set the instance to null so that we definitely get a new one for
     * the next test.
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
    public function test_it_constructs()
    {
        $this->assertInstanceOf('\App\Http\Controllers\Mship\Authentication', $this->authenticationInstance);
    }

    /** @test **/
    public function test_get_redirect_goes_to_login_if_no_auth()
    {
        // In this test, we assert that we're getting a redirect to the login page if no auth
        $expectedStatus = 302;
        $expectedRedirectRegxp = '#\h*<title>Redirecting to http:\/\/.*?\/mship\/auth\/login<\/title>\h*#';
        Auth::shouldReceive('check')->once()->andReturn(false);
        
        $result = $this->authenticationInstance->getRedirect();
        $this->assertEquals($expectedStatus, $result->status());
        $this->assertRegExp($expectedRedirectRegxp, $result->content());
    }

    /** @test **/
    public function test_get_redirect_goes_to_secondary_login_if_there_is_none_and_password_set()
    {
        // In this test, we assert that we're getting a redirect to the secondary password page if needed
        $expectedStatus = 302;
        $expectedRedirectRegxp = '#\h*<title>Redirecting to http:\/\/.*?\/mship\/security\/auth<\/title>\h*#';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[withIp, where, count, hasPassword, getLastLoginIpAttribute, load, save]');
        $account->shouldReceive('hasPassword')->once()->andReturn(true);
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->shouldReceive('getLastLoginIpAttribute')->times(2)->andReturn(2);
        $account->makePartial();

        Auth::shouldReceive('check')->times(2)->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($account);

        // We need to call the middleware callback so set $this->_account in $authenticationInstance
        $callback = $this->authenticationInstance->getMiddleware()[0]['middleware'];
        $callback(null, function(){});
        $result = $this->authenticationInstance->getRedirect();
        $this->assertEquals($expectedStatus, $result->status());
        $this->assertRegExp($expectedRedirectRegxp, $result->content());
    }

}
