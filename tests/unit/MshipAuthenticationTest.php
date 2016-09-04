<?php

use App\Models\Mship\Account;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
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
    public function test_it_constructs()
    {
        $this->assertInstanceOf('\App\Http\Controllers\Mship\Authentication', $this->authenticationInstance);
    }

    /** @test **/
    public function test_get_redirect_goes_to_login_if_no_auth()
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
    public function test_get_redirect_goes_to_secondary_login_if_there_is_none_and_password_set()
    {
        /*
         * In this test, we assert that we're getting a redirect to the secondary password page if one is
         * needed but not provided
         */
        $expectedStatus = 302;
        $expectedRedirectRegExp = '#\h*<title>Redirecting to http:\/\/.*?\/mship\/security\/auth<\/title>\h*#';
        $expectedObjectType = 'Illuminate\Http\RedirectResponse';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword, getLastLoginIpAttribute, load, save]');
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
        $this->assertInstanceOf($expectedObjectType, $result);
        $this->assertEquals($expectedStatus, $result->status());
        $this->assertRegExp($expectedRedirectRegExp, $result->content());
    }

    /** @test **/
    public function test_get_redirect_goes_to_secondary_password_setting_page_if_not_set_but_mandatory()
    {
        // In this test, we assert that we're getting a redirect to the secondary password replace page
        $expectedStatus = 302;
        $expectedRedirectRegExp = '#\h*<title>Redirecting to http:\/\/.*?\/mship\/security\/replace<\/title>\h*#';
        $expectedObjectType = 'Illuminate\Http\RedirectResponse';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword, getLastLoginIpAttribute, load, save, getMandatoryPasswordAttribute]');
        $account->shouldReceive('hasPassword')->times(2)->andReturn(false);
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->shouldReceive('getLastLoginIpAttribute')->times(2)->andReturn(2);
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(true);
        $account->makePartial();

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
    public function test_get_redirect_goes_to_dashboard_if_no_notifications()
    {
        // In this test, we assert that we're getting a redirect to the dashboard
        $expectedStatus = 302;
        $expectedRedirectRegExp = '#\h*<title>Redirecting to http:\/\/.*?\/mship\/manage\/dashboard<\/title>\h*#';
        $expectedObjectType = 'Illuminate\Http\RedirectResponse';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword, getLastLoginIpAttribute, load, save, getMandatoryPasswordAttribute]');
        $account->shouldReceive('hasPassword')->times(2)->andReturn(false);
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->shouldReceive('getLastLoginIpAttribute')->times(2)->andReturn(2);
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(false);
        $account->makePartial();

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
    public function test_get_redirect_goes_to_notifications_if_must_acknowledge_notifications_present()
    {
        // In this test, we assert that we're getting a redirect to the notification page
        $expectedStatus = 302;
        $expectedRedirectRegExp = '#\h*<title>Redirecting to http:\/\/.*?\/mship\/notification\/list<\/title>\h*#';
        $expectedObjectType = 'Illuminate\Http\RedirectResponse';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock(
            'App\Models\Mship\Account[hasPassword, getLastLoginIpAttribute, load, save, getMandatoryPasswordAttribute, getHasUnreadImportantNotificationsAttribute, getHasUnreadMustAcknowledgeNotificationsAttribute]'
        );
        $account->shouldReceive('hasPassword')->times(2)->andReturn(false);
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->shouldReceive('getLastLoginIpAttribute')->times(2)->andReturn(2);
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(false);
        $account->shouldReceive('getHasUnreadImportantNotificationsAttribute')->once()->andReturn(false);
        $account->shouldReceive('getHasUnreadMustAcknowledgeNotificationsAttribute')->once()->andReturn(true);
        $account->makePartial();

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
    public function test_get_redirect_goes_to_notifications_if_important_notifications_present()
    {
        // In this test, we assert that we're getting a redirect to the notification page
        $expectedStatus = 302;
        $expectedRedirectRegExp = '#\h*<title>Redirecting to http:\/\/.*?\/mship\/notification\/list<\/title>\h*#';
        $expectedObjectType = 'Illuminate\Http\RedirectResponse';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock(
            'App\Models\Mship\Account[hasPassword, getLastLoginIpAttribute, load, save, getMandatoryPasswordAttribute, getHasUnreadImportantNotificationsAttribute, getHasUnreadMustAcknowledgeNotificationsAttribute]'
        );
        $account->shouldReceive('hasPassword')->times(2)->andReturn(false);
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->shouldReceive('getLastLoginIpAttribute')->times(2)->andReturn(2);
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(false);
        $account->shouldReceive('getHasUnreadImportantNotificationsAttribute')->once()->andReturn(true);
        $account->shouldReceive('getHasUnreadMustAcknowledgeNotificationsAttribute')->never();
        $account->makePartial();

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
    public function test_get_redirect_sets_auth_extra_to_false_if_no_secondary_password()
    {
        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock(
            'App\Models\Mship\Account[hasPassword, getLastLoginIpAttribute, load, save, getMandatoryPasswordAttribute, getHasUnreadImportantNotificationsAttribute, getHasUnreadMustAcknowledgeNotificationsAttribute]'
        );
        $account->shouldReceive('hasPassword')->once()->andReturn(false);
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->shouldReceive('getLastLoginIpAttribute')->times(2)->andReturn(2);
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(false);
        $account->shouldReceive('getHasUnreadImportantNotificationsAttribute')->once()->andReturn(true);
        $account->shouldReceive('getHasUnreadMustAcknowledgeNotificationsAttribute')->never();
        $account->makePartial();

        Auth::shouldReceive('check')->times(2)->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($account);
        Session::set('auth_extra', Carbon::now());

        // We need to call the middleware callback so set $this->_account in $authenticationInstance
        $callback = $this->authenticationInstance->getMiddleware()[0]['middleware'];
        $callback(null, function(){});
        $this->authenticationInstance->getRedirect();
        $this->assertFalse(Session::get('auth_extra'));
    }

    /** @test **/
    public function test_get_redirect_forgets_auth_extra_if_secondary_password_has_expired()
    {
        // In this test, we assert that we're getting a redirect to the redirect if a secondary password has expired
        $expectedStatus = 302;
        $expectedRedirectRegExp = '#\h*<title>Redirecting to http:\/\/.*?\/mship\/auth\/redirect<\/title>\h*#';
        $expectedObjectType = 'Illuminate\Http\RedirectResponse';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock(
            'App\Models\Mship\Account[hasPassword, getLastLoginIpAttribute, load, save, getMandatoryPasswordAttribute, getHasUnreadImportantNotificationsAttribute, getHasUnreadMustAcknowledgeNotificationsAttribute]'
        );
        $account->shouldReceive('hasPassword')->never();
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->shouldReceive('getLastLoginIpAttribute')->times(2)->andReturn(2);
        $account->makePartial();

        Auth::shouldReceive('check')->times(2)->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($account);
        Session::set('auth_extra', Carbon::now()->subHours(5));

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
    public function test_get_redirect_redirects_to_itself_if_secondary_password_has_expired()
    {
        // In this test, we assert that we're getting a redirect to the redirect if a secondary password has expired
        $expectedStatus = 302;
        $expectedRedirectRegExp = '#\h*<title>Redirecting to http:\/\/.*?\/mship\/auth\/redirect<\/title>\h*#';
        $expectedObjectType = 'Illuminate\Http\RedirectResponse';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock(
            'App\Models\Mship\Account[hasPassword, getLastLoginIpAttribute, load, save, getMandatoryPasswordAttribute, getHasUnreadImportantNotificationsAttribute, getHasUnreadMustAcknowledgeNotificationsAttribute]'
        );
        $account->shouldReceive('hasPassword')->never();
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->shouldReceive('getLastLoginIpAttribute')->times(2)->andReturn(2);
        $account->makePartial();

        Auth::shouldReceive('check')->times(2)->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($account);
        Session::set('auth_extra', Carbon::now()->subHours(5));

        // We need to call the middleware callback (function in constructor of BaseController) to set $this->_account in $authenticationInstance
        $callback = $this->authenticationInstance->getMiddleware()[0]['middleware'];
        $callback(null, function(){});
        $result = $this->authenticationInstance->getRedirect();
        $this->assertInstanceOf($expectedObjectType, $result);
        $this->assertEquals($expectedStatus, $result->status());
        $this->assertRegExp($expectedRedirectRegExp, $result->content());
    }

    /** @test **/
    public function test_get_redirect_forgets_duplicate_ip_when_going_to_dashboard()
    {
        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword, getLastLoginIpAttribute, load, save, getMandatoryPasswordAttribute]');
        $account->shouldReceive('hasPassword')->times(2)->andReturn(false);
        $account->shouldReceive('load')->once()->andReturnNull();
        $account->shouldReceive('save')->once()->andReturnNull();
        $account->shouldReceive('getLastLoginIpAttribute')->times(2)->andReturn(2);
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(false);
        $account->makePartial();

        Auth::shouldReceive('check')->times(2)->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($account);
        Session::set('auth_duplicate_ip', true);

        // We need to call the middleware callback so set $this->_account in $authenticationInstance
        $callback = $this->authenticationInstance->getMiddleware()[0]['middleware'];
        $callback(null, function(){});
        $this->authenticationInstance->getRedirect();
        $this->assertFalse(Session::has('auth_duplicate_ip'));
    }

}
