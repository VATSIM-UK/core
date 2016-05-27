<?php

use App\Models\Mship\Account;
use App\Models\Mship\Account\Email;
use App\Models\Sso\Account as SsoAccount;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class SsoEmailTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    protected $account, $email;

    public function setUp()
    {
        parent::setUp();

        $this->account = factory(Account::class)->create();
        $this->email = factory(Email::class)->make();
        $this->account->secondaryEmails()->save($this->email);
        $this->account = $this->account->fresh();
    }

    public function testAssignmentPageLoads()
    {
        $this->visit('/mship/manage/email/assignments')
            ->seePageIs('/mship/manage/email/assignments')
            ->assertResponseOk();
    }
    
    public function testPrimaryAndSecondaryAssignmentSuccessful()
    {
        // secondary
        $this->assertEquals(0, count($this->account->ssoEmails));
        $this->actingAs($this->account)
            ->visit('/mship/manage/email/assignments')
            ->select($this->email->id, 'assign_' . SsoAccount::orderBy('id')->first()->id)
            ->press('Save Assignments')
            ->see('Success!');

        $this->account = $this->account->fresh();
        $this->assertEquals(1, count($this->account->ssoEmails));

        // primary

        $this->actingAs($this->account)
            ->visit('/mship/manage/email/assignments')
            ->select('pri', 'assign_' . SsoAccount::orderBy('id')->first()->id)
            ->press('Save Assignments')
            ->see('Success!');

        $this->account = $this->account->fresh();
        $this->assertEquals(0, count($this->account->ssoEmails));
    }
}
