<?php

namespace Tests\Unit;

use App\Models\Mship\Account;
use Auth;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Mockery;
use Tests\TestCase;

class MshipAuthenticationTest extends TestCase
{
    use DatabaseTransactions;

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
    public function it_constructs()
    {
        $this->assertInstanceOf('\App\Http\Controllers\Mship\Authentication', $this->authenticationInstance);
    }

    /** @test **/
    public function it_redirects_to_login_if_no_auth()
    {
        // In this test, we assert that we're getting a redirect to the login page if no auth at all
        $expectedStatus = 302;
        $expectedRedirectRegExp = '#\h*<title>Redirecting to http:\/\/.*?\/mship\/auth\/login<\/title>\h*#';
        $expectedObjectType = 'Illuminate\Http\RedirectResponse';
        Auth::shouldReceive('check')->once()->andReturn(false);

        $result = $this->authenticationInstance->getRedirect();

        $this->assertInstanceOf($expectedObjectType, $result);
        $this->assertEquals($expectedStatus, $result->status());
        $this->assertRegExp($expectedRedirectRegExp, $result->content());
    }

    /** @test **/
    public function it_redirects_to_secondary_login_if_hasnt_already_been_done_and_password_set()
    {
        /*
         * In this test, we assert that we're getting a redirect to the secondary password page if one is
         * needed but not provided
         */
        $expectedStatus = 302;
        $expectedRedirectRegExp = '#\h*<title>Redirecting to http:\/\/.*?\/mship\/security\/auth<\/title>\h*#';
        $expectedObjectType = 'Illuminate\Http\RedirectResponse';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword, load, save]');
        $account->shouldReceive('hasPassword')->once()->andReturn(true);
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->makePartial();

        // Facades are already setup to be mocks, so just tell it what to expect
        Auth::shouldReceive('check')->times(2)->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($account);

        // We need to call the middleware callback so set $this->_account in $authenticationInstance
        $callback = $this->authenticationInstance->getMiddleware()[0]['middleware'];
        $callback(null, function(){});
        $result = $this->authenticationInstance->getRedirect();
        $this->assertInstanceOf($expectedObjectType, $result);
        $this->assertEquals($expectedStatus, $result->status());
        $this->assertRegExp($expectedRedirectRegExp, $result->content());
    }

    /** @test **/
    public function it_redirects_to_secondary_password_setting_page_if_not_set_but_mandatory()
    {
        // In this test, we assert that we're getting a redirect to the secondary password replace page
        $expectedStatus = 302;
        $expectedRedirectRegExp = '#\h*<title>Redirecting to http:\/\/.*?\/mship\/security\/replace<\/title>\h*#';
        $expectedObjectType = 'Illuminate\Http\RedirectResponse';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword, load, save, getMandatoryPasswordAttribute]');
        $account->shouldReceive('hasPassword')->times(2)->andReturn(false);
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(true);
        $account->makePartial();

        // Facades are already setup to be mocks, so just tell it what to expect
        Auth::shouldReceive('check')->times(2)->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($account);

        // We need to call the middleware callback so set $this->_account in $authenticationInstance
        $callback = $this->authenticationInstance->getMiddleware()[0]['middleware'];
        $callback(null, function(){});
        $result = $this->authenticationInstance->getRedirect();
        $this->assertInstanceOf($expectedObjectType, $result);
        $this->assertEquals($expectedStatus, $result->status());
        $this->assertRegExp($expectedRedirectRegExp, $result->content());
    }

    /** @test **/
    public function it_redirects_to_dashboard_if_no_notifications()
    {
        // In this test, we assert that we're getting a redirect to the dashboard
        $expectedStatus = 302;
        $expectedRedirectRegExp = '#\h*<title>Redirecting to http:\/\/.*?\/mship\/manage\/dashboard<\/title>\h*#';
        $expectedObjectType = 'Illuminate\Http\RedirectResponse';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword, load, save, getMandatoryPasswordAttribute, getHasUnreadImportantNotificationsAttribute, getHasUnreadMustAcknowledgeNotificationsAttribute]');
        $account->shouldReceive('hasPassword')->times(2)->andReturn(false);
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(false);
        $account->shouldReceive('getHasUnreadImportantNotificationsAttribute')->once()->andReturn(false);
        $account->shouldReceive('getHasUnreadMustAcknowledgeNotificationsAttribute')->once()->andReturn(false);
        $account->makePartial();

        // Facades are already setup to be mocks, so just tell it what to expect
        Auth::shouldReceive('check')->times(2)->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($account);

        // We need to call the middleware callback so set $this->_account in $authenticationInstance
        $callback = $this->authenticationInstance->getMiddleware()[0]['middleware'];
        $callback(null, function(){});
        $result = $this->authenticationInstance->getRedirect();
        $this->assertInstanceOf($expectedObjectType, $result);
        $this->assertEquals($expectedStatus, $result->status());
        $this->assertRegExp($expectedRedirectRegExp, $result->content());
    }

    /** @test **/
    public function it_redirects_to_notifications_if_must_acknowledge_notifications_present()
    {
        // In this test, we assert that we're getting a redirect to the notification page
        $expectedStatus = 302;
        $expectedRedirectRegExp = '#\h*<title>Redirecting to http:\/\/.*?\/mship\/notification\/list<\/title>\h*#';
        $expectedObjectType = 'Illuminate\Http\RedirectResponse';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock(
            'App\Models\Mship\Account[hasPassword, load, save, getMandatoryPasswordAttribute, getHasUnreadImportantNotificationsAttribute, getHasUnreadMustAcknowledgeNotificationsAttribute]'
        );
        $account->shouldReceive('hasPassword')->times(2)->andReturn(false);
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(false);
        $account->shouldReceive('getHasUnreadImportantNotificationsAttribute')->once()->andReturn(false);
        $account->shouldReceive('getHasUnreadMustAcknowledgeNotificationsAttribute')->once()->andReturn(true);
        $account->makePartial();

        // Facades are already setup to be mocks, so just tell it what to expect
        Auth::shouldReceive('check')->times(2)->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($account);

        // We need to call the middleware callback so set $this->_account in $authenticationInstance
        $callback = $this->authenticationInstance->getMiddleware()[0]['middleware'];
        $callback(null, function(){});
        $result = $this->authenticationInstance->getRedirect();
        $this->assertInstanceOf($expectedObjectType, $result);
        $this->assertEquals($expectedStatus, $result->status());
        $this->assertRegExp($expectedRedirectRegExp, $result->content());
    }

    /** @test **/
    public function it_redirects_to_notifications_if_important_notifications_present()
    {
        // In this test, we assert that we're getting a redirect to the notification page
        $expectedStatus = 302;
        $expectedRedirectRegExp = '#\h*<title>Redirecting to http:\/\/.*?\/mship\/notification\/list<\/title>\h*#';
        $expectedObjectType = 'Illuminate\Http\RedirectResponse';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock(
            'App\Models\Mship\Account[hasPassword, load, save, getMandatoryPasswordAttribute, getHasUnreadImportantNotificationsAttribute, getHasUnreadMustAcknowledgeNotificationsAttribute]'
        );
        $account->shouldReceive('hasPassword')->times(2)->andReturn(false);
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(false);
        $account->shouldReceive('getHasUnreadImportantNotificationsAttribute')->once()->andReturn(true);
        $account->shouldReceive('getHasUnreadMustAcknowledgeNotificationsAttribute')->never();
        $account->makePartial();

        // Facades are already setup to be mocks, so just tell it what to expect
        Auth::shouldReceive('check')->times(2)->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($account);

        // We need to call the middleware callback so set $this->_account in $authenticationInstance
        $callback = $this->authenticationInstance->getMiddleware()[0]['middleware'];
        $callback(null, function(){});
        $result = $this->authenticationInstance->getRedirect();
        $this->assertInstanceOf($expectedObjectType, $result);
        $this->assertEquals($expectedStatus, $result->status());
        $this->assertRegExp($expectedRedirectRegExp, $result->content());
    }

    /** @test **/
    public function it_sets_auth_extra_to_false_if_no_secondary_password_is_set()
    {
        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock(
            'App\Models\Mship\Account[hasPassword, load, save, getMandatoryPasswordAttribute, getHasUnreadImportantNotificationsAttribute, getHasUnreadMustAcknowledgeNotificationsAttribute]'
        );
        $account->shouldReceive('hasPassword')->once()->andReturn(false);
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(false);
        $account->shouldReceive('getHasUnreadImportantNotificationsAttribute')->once()->andReturn(true);
        $account->shouldReceive('getHasUnreadMustAcknowledgeNotificationsAttribute')->never();
        $account->makePartial();

        // Facades are already setup to be mocks, so just tell it what to expect, set the Session data
        Auth::shouldReceive('check')->times(2)->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($account);
        Session::put('auth_extra', Carbon::now());

        // We need to call the middleware callback so set $this->_account in $authenticationInstance
        $callback = $this->authenticationInstance->getMiddleware()[0]['middleware'];
        $callback(null, function(){});
        $this->authenticationInstance->getRedirect();
        $this->assertFalse(Session::get('auth_extra'));
    }

    /** @test **/
    public function it_forgets_auth_extra_if_secondary_password_has_expired()
    {
        // In this test, we assert that we're getting a redirect to the redirect if a secondary password has expired
        $expectedStatus = 302;
        $expectedRedirectRegExp = '#\h*<title>Redirecting to http:\/\/.*?\/mship\/auth\/redirect<\/title>\h*#';
        $expectedObjectType = 'Illuminate\Http\RedirectResponse';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock(
            'App\Models\Mship\Account[hasPassword, load, save, getMandatoryPasswordAttribute, getHasUnreadImportantNotificationsAttribute, getHasUnreadMustAcknowledgeNotificationsAttribute]'
        );
        $account->shouldReceive('hasPassword')->never();
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->makePartial();

        // Facades are already setup to be mocks, so just tell it what to expect, set the Session data
        Auth::shouldReceive('check')->times(2)->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($account);
        Session::put('auth_extra', Carbon::now()->subHours(5));

        // We need to call the middleware callback so set $this->_account in $authenticationInstance
        $callback = $this->authenticationInstance->getMiddleware()[0]['middleware'];
        $callback(null, function(){});
        $result = $this->authenticationInstance->getRedirect();
        $this->assertFalse(Session::has('auth_extra'));
        $this->assertInstanceOf($expectedObjectType, $result);
        $this->assertEquals($expectedStatus, $result->status());
        $this->assertRegExp($expectedRedirectRegExp, $result->content());
    }

    /** @test **/
    public function it_redirects_to_itself_if_secondary_password_has_expired()
    {
        // In this test, we assert that we're getting a redirect to the redirect if a secondary password has expired
        $expectedStatus = 302;
        $expectedRedirectRegExp = '#\h*<title>Redirecting to http:\/\/.*?\/mship\/auth\/redirect<\/title>\h*#';
        $expectedObjectType = 'Illuminate\Http\RedirectResponse';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock(
            'App\Models\Mship\Account[hasPassword, load, save, getMandatoryPasswordAttribute, getHasUnreadImportantNotificationsAttribute, getHasUnreadMustAcknowledgeNotificationsAttribute]'
        );
        $account->shouldReceive('hasPassword')->never();
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->makePartial();

        // Facades are already setup to be mocks, so just tell it what to expect, set the Session data
        Auth::shouldReceive('check')->times(2)->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($account);
        Session::put('auth_extra', Carbon::now()->subHours(5));

        // We need to call the middleware callback (function in constructor of BaseController) to set $this->_account in $authenticationInstance
        $callback = $this->authenticationInstance->getMiddleware()[0]['middleware'];
        $callback(null, function(){});
        $result = $this->authenticationInstance->getRedirect();
        $this->assertInstanceOf($expectedObjectType, $result);
        $this->assertEquals($expectedStatus, $result->status());
        $this->assertRegExp($expectedRedirectRegExp, $result->content());
    }

    /** @test **/
    public function it_forgets_duplicate_ip_when_going_to_dashboard()
    {
        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword, load, save, getMandatoryPasswordAttribute]');
        $account->shouldReceive('hasPassword')->times(2)->andReturn(false);
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(false);
        $account->makePartial();

        // Facades are already setup to be mocks, so just tell it what to expect, set the Session data
        Auth::shouldReceive('check')->times(2)->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($account);
        Session::put('auth_duplicate_ip', true);

        // We need to call the middleware callback so set $this->_account in $authenticationInstance
        $callback = $this->authenticationInstance->getMiddleware()[0]['middleware'];
        $callback(null, function(){});
        $this->authenticationInstance->getRedirect();
        $this->assertFalse(Session::has('auth_duplicate_ip'));
    }

}
