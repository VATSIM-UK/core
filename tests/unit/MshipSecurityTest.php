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

    public function test_get_replace_returns_replace_sls_type_when_password_set_and_is_non_mandatory()
    {
        $expected = 'replace';

        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->once()->andReturn(true);
        $account->shouldReceive('hasPasswordExpired')->once()->andReturn(false);
        $account->shouldReceive('getMandatoryPasswordAttribute')->never()->andReturn(false);
        Auth::shouldReceive('user')->times(2)->andReturn($account);

        $result = $this->securityInstance->getReplace()->getData()['sls_type'];
        $this->assertEquals($expected, $result);
    }

    public function test_get_replace_returns_replace_sls_type_when_password_set_and_is_mandatory()
    {
        $expected = 'replace';

        $account = Mockery::mock('App\Models\Mship\Account[hasPassword,getMandatoryPasswordAttribute, hasPasswordExpired]');
        $account->shouldReceive('hasPassword')->once()->andReturn(true);
        $account->shouldReceive('hasPasswordExpired')->once()->andReturn(false);
        $account->shouldReceive('getMandatoryPasswordAttribute')->never()->andReturn(true);
        Auth::shouldReceive('user')->times(2)->andReturn($account);

        $result = $this->securityInstance->getReplace()->getData()['sls_type'];
        $this->assertEquals($expected, $result);
    }


}
