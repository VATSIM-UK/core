<?php

use Illuminate\Support\Facades\Auth;
use App\Models\Mship\Account;
class MshipSecurityTest extends TestCase
{
    /**
     * @var \App\Http\Controllers\Mship\Security
     */
    protected $securityInstance;

    public function setUp()
    {
        parent::setUp();
        $this->securityInstance = new \App\Http\Controllers\Mship\Security();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->securityInstance = null;
    }

    /** @test  */
    public function test_it_constructs()
    {
        $this->assertInstanceOf('\App\Http\Controllers\Mship\Security', $this->securityInstance);
    }

    public function test_get_replace_sets_redirect_when_attempting_to_disable_but_password_is_mandatory()
    {
        $expectedError = 'You cannot disable your secondary password.';
        $expectedStatus = 302;

        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->never();
        $account->shouldReceive('hasPasswordExpired')->never();
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(true);
        Auth::shouldReceive('user')->once()->andReturn($account);

        $result = $this->securityInstance->getReplace(true);
        $this->assertEquals($expectedStatus, $result->status());
        $this->assertEquals($expectedError, $result->getSession()->get('error'));
    }

    public function test_get_replace_sets_disable_false_when_attempting_to_disable_but_no_password_set()
    {
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->times(2)->andReturn(false);
        $account->shouldReceive('hasPasswordExpired')->never();
        $account->shouldReceive('getMandatoryPasswordAttribute')->times(2)->andReturn(false);
        Auth::shouldReceive('user')->times(4)->andReturn($account);

        $result = $this->securityInstance->getReplace(true)->getData()['disable'];
        $this->assertFalse($result);
    }

    public function test_get_replace_sets_disable_true_when_attempting_to_disable_and_password_set()
    {
        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->times(2)->andReturn(true);
        $account->shouldReceive('hasPasswordExpired')->once()->andReturn(false);
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(false);
        Auth::shouldReceive('user')->times(4)->andReturn($account);

        $result = $this->securityInstance->getReplace(true)->getData()['disable'];
        $this->assertTrue($result);
    }

    public function test_get_replace_sets_disable_sls_type_when_attempting_to_disable_and_password_set()
    {
        $expected = 'disable';

        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->times(2)->andReturn(true);
        $account->shouldReceive('hasPasswordExpired')->once()->andReturn(false);
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(false);
        Auth::shouldReceive('user')->times(4)->andReturn($account);

        $result = $this->securityInstance->getReplace(true)->getData()['sls_type'];
        $this->assertEquals($expected, $result);
    }

    public function test_get_replace_does_not_set_disable_sls_type_when_attempting_to_disable_and_password_not_set()
    {
        $expected = 'requested';

        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->times(2)->andReturn(false);
        $account->shouldReceive('hasPasswordExpired')->never()->andReturn(false);
        $account->shouldReceive('getMandatoryPasswordAttribute')->times(2)->andReturn(false);
        Auth::shouldReceive('user')->times(4)->andReturn($account);

        $result = $this->securityInstance->getReplace(true)->getData()['sls_type'];
        $this->assertEquals($expected, $result);
    }


    public function test_get_replace_sets_replace_sls_type_when_password_set_and_is_non_mandatory()
    {
        $expected = 'replace';

        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->once()->andReturn(true);
        $account->shouldReceive('hasPasswordExpired')->once()->andReturn(false);
        $account->shouldReceive('getMandatoryPasswordAttribute')->never();
        Auth::shouldReceive('user')->times(2)->andReturn($account);

        $result = $this->securityInstance->getReplace()->getData()['sls_type'];
        $this->assertEquals($expected, $result);
    }

    public function test_get_replace_sets_replace_sls_type_when_password_set_and_is_mandatory()
    {
        $expected = 'replace';

        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->once()->andReturn(true);
        $account->shouldReceive('hasPasswordExpired')->once()->andReturn(false);
        $account->shouldReceive('getMandatoryPasswordAttribute')->never();
        Auth::shouldReceive('user')->times(2)->andReturn($account);

        $result = $this->securityInstance->getReplace()->getData()['sls_type'];
        $this->assertEquals($expected, $result);
    }

    public function test_get_replace_sets_requested_sls_type_when_password_not_set_and_is_non_mandatory()
    {
        $expected = 'requested';

        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->once()->andReturn(false);
        $account->shouldReceive('hasPasswordExpired')->never();
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(false);
        Auth::shouldReceive('user')->times(2)->andReturn($account);

        $result = $this->securityInstance->getReplace()->getData()['sls_type'];
        $this->assertEquals($expected, $result);
    }

    public function test_get_replace_sets_forced_sls_type_when_password_not_set_and_is_mandatory()
    {
        $expected = 'forced';

        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->once()->andReturn(false);
        $account->shouldReceive('hasPasswordExpired')->never();
        $account->shouldReceive('getMandatoryPasswordAttribute')->once()->andReturn(true);
        Auth::shouldReceive('user')->times(2)->andReturn($account);

        $result = $this->securityInstance->getReplace()->getData()['sls_type'];
        $this->assertEquals($expected, $result);
    }

    public function test_get_replace_sets_forced_sls_type_when_password_set_and_has_expired()
    {
        $expected = 'expired';

        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->once()->andReturn(true);
        $account->shouldReceive('hasPasswordExpired')->once()->andReturn(true);
        $account->shouldReceive('getMandatoryPasswordAttribute')->never();
        Auth::shouldReceive('user')->times(2)->andReturn($account);

        $result = $this->securityInstance->getReplace()->getData()['sls_type'];
        $this->assertEquals($expected, $result);
    }

}
