<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use App\Models\Mship\Account;
use Mockery;
use Tests\TestCase;

class MshipSecurityTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Instance used for the testsl
     *
     * @var \App\Http\Controllers\Mship\Security
     */
    protected $securityInstance;

    /**
     * Nothing fancy here, just create an instance of the class at the
     * the start of the test so we don't have to create a new one for every
     * single test.
     */
    public function setUp()
    {
        parent::setUp();
        $this->securityInstance = new \App\Http\Controllers\Mship\Security();
    }

    /**
     * Set the instance to null so that we definitely get a new one for
     * the next test.
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->securityInstance = null;
    }

    /**
     * Exactly what it says on the tin...
     *
     * @test
     **/
    public function it_constructs()
    {
        $this->assertInstanceOf('\App\Http\Controllers\Mship\Security', $this->securityInstance);
    }

    /** @test **/
    public function it_sets_redirect_when_attempting_to_disable_but_password_is_mandatory()
    {
        // We're expecting a redirect and some sort of error message, so we'll check for these.
        $expectedError = 'You cannot disable your secondary password.';
        $expectedStatus = 302;
        $expectedObjectType = 'Illuminate\Http\RedirectResponse';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->never();
        $account->shouldReceive('hasPasswordExpired')->never();
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($account);

        $result = $this->securityInstance->getReplace(true);
        $this->assertInstanceOf('Illuminate\Http\RedirectResponse', $result);
        $this->assertEquals($expectedStatus, $result->status());
        $this->assertEquals($expectedError, $result->getSession()->get('error'));
    }

    /** @test **/
    public function it_sets_disable_variable_to_false_when_attempting_to_disable_secondary_password_but_no_password_set()
    {
        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->times(2)->andReturn(false);
        $account->shouldReceive('hasPasswordExpired')->never();
        $account->shouldReceive('getMandatoryPasswordAttribute')->times(2)->andReturn(false);
        Auth::shouldReceive('user')->times(4)->andReturn($account);

        // Make an assertion based on the data that is passed to the View returned by the method
        $result = $this->securityInstance->getReplace(true)->getData()['disable'];
        $this->assertFalse($result);
    }

    /** @test **/
    public function it_sets_disable_to_true_when_attempting_to_disable_secondary_password_and_password_set()
    {
        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->times(2)->andReturn(true);
        $account->shouldReceive('hasPasswordExpired')->once()->andReturn(false);
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(false);
        Auth::shouldReceive('user')->times(4)->andReturn($account);

        // Make an assertion based on the data that is passed to the View returned by the method
        $result = $this->securityInstance->getReplace(true)->getData()['disable'];
        $this->assertTrue($result);
    }

    /** @test **/
    public function it_sets_disable_sls_type_when_attempting_to_disable_and_password_set()
    {
        $expected = 'disable';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->times(2)->andReturn(true);
        $account->shouldReceive('hasPasswordExpired')->once()->andReturn(false);
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(false);
        Auth::shouldReceive('user')->times(4)->andReturn($account);

        // Make an assertion based on the data that is passed to the View returned by the method
        $result = $this->securityInstance->getReplace(true)->getData()['sls_type'];
        $this->assertEquals($expected, $result);
    }

    /** @test **/
    public function it_does_not_set_disable_sls_type_when_attempting_to_disable_and_password_not_set()
    {
        $expected = 'requested';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->times(2)->andReturn(false);
        $account->shouldReceive('hasPasswordExpired')->never()->andReturn(false);
        $account->shouldReceive('getMandatoryPasswordAttribute')->times(2)->andReturn(false);
        Auth::shouldReceive('user')->times(4)->andReturn($account);

        // Make an assertion based on the data that is passed to the View returned by the method
        $result = $this->securityInstance->getReplace(true)->getData()['sls_type'];
        $this->assertEquals($expected, $result);
    }

    /** @test **/
    public function it_sets_replace_sls_type_when_password_set_and_is_non_mandatory()
    {
        $expected = 'replace';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->once()->andReturn(true);
        $account->shouldReceive('hasPasswordExpired')->once()->andReturn(false);
        $account->shouldReceive('getMandatoryPasswordAttribute')->never();
        Auth::shouldReceive('user')->times(2)->andReturn($account);

        // Make an assertion based on the data that is passed to the View returned by the method
        $result = $this->securityInstance->getReplace()->getData()['sls_type'];
        $this->assertEquals($expected, $result);
    }

    /** @test **/
    public function it_sets_replace_sls_type_when_password_set_and_is_mandatory()
    {
        $expected = 'replace';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->once()->andReturn(true);
        $account->shouldReceive('hasPasswordExpired')->once()->andReturn(false);
        $account->shouldReceive('getMandatoryPasswordAttribute')->never();
        Auth::shouldReceive('user')->times(2)->andReturn($account);

        // Make an assertion based on the data that is passed to the View returned by the method
        $result = $this->securityInstance->getReplace()->getData()['sls_type'];
        $this->assertEquals($expected, $result);
    }

    /** @test **/
    public function it_sets_requested_sls_type_when_password_not_set_and_is_non_mandatory()
    {
        $expected = 'requested';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->once()->andReturn(false);
        $account->shouldReceive('hasPasswordExpired')->never();
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(false);
        Auth::shouldReceive('user')->times(2)->andReturn($account);

        // Make an assertion based on the data that is passed to the View returned by the method
        $result = $this->securityInstance->getReplace()->getData()['sls_type'];
        $this->assertEquals($expected, $result);
    }

    /** @test **/
    public function it_sets_forced_sls_type_when_password_not_set_and_is_mandatory()
    {
        $expected = 'forced';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->once()->andReturn(false);
        $account->shouldReceive('hasPasswordExpired')->never();
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(true);
        Auth::shouldReceive('user')->times(2)->andReturn($account);

        // Make an assertion based on the data that is passed to the View returned by the method
        $result = $this->securityInstance->getReplace()->getData()['sls_type'];
        $this->assertEquals($expected, $result);
    }

    /** @test **/
    public function it_sets_expired_sls_type_when_password_set_and_has_expired()
    {
        $expected = 'expired';

        // Set up the account mock and predict how many times each of the mocked methods will be called
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->once()->andReturn(true);
        $account->shouldReceive('hasPasswordExpired')->once()->andReturn(true);
        $account->shouldReceive('getMandatoryPasswordAttribute')->never();
        Auth::shouldReceive('user')->times(2)->andReturn($account);

        // Make an assertion based on the data that is passed to the View returned by the method
        $result = $this->securityInstance->getReplace()->getData()['sls_type'];
        $this->assertEquals($expected, $result);
    }

}
