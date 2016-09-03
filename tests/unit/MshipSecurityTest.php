<?php


use Illuminate\Support\Facades\Auth;
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

    public function test_it_does_something()
    {
        $expected = 'replace';

        $account = factory(\App\Models\Mship\Account::class)->create(['password' => 'testpass']);
        $this->actingAs($account);

        Auth::shouldReceive('user')->times(2)->andReturn($account);

        $result = $this->securityInstance->getReplace()->getData()['sls_type'];
        $this->assertEquals($expected, $result);
    }


}
