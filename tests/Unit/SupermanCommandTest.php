<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Mship\Account;
use Spatie\Permission\Models\Role;
use Laravel\BrowserKitTesting\DatabaseTransactions;

class SupermanCommandTest extends TestCase
{
    use DatabaseTransactions;

    private $account;

    protected function setUp()
    {
        parent::setUp();

        $this->account = factory(Account::class)->create();
    }

    /** @test **/
    public function itReportsToConsoleWhenSuccessful()
    {
        $this->artisan('grant:superman', ['cid' => $this->account->id])
            ->expectsOutput('Account added to the superman role!');
    }

    /** @test **/
    public function itReportsToConsoleWhenRoleAlreadyFound()
    {
        $this->account->assignRole(Role::findByName('privacc'));

        $this->artisan('grant:superman', ['cid' => $this->account->id])
            ->expectsOutput('The specified account already has the "superman" role.');
    }

    /** @test **/
    public function itReportsToConsoleWhenTheCIDIsNotFound()
    {
        $this->artisan('grant:superman', ['cid' => 0000000])
            ->expectsOutput('The specific CID was not found.');
    }

    /** @test **/
    public function itAttachesRoleSuccessfully()
    {
        $this->artisan('grant:superman', ['cid' => $this->account->id])
            ->expectsOutput('Account added to the superman role!');

        $this->assertDatabaseHas('mship_account_role', [
            'model_id' => $this->account->id,
            'role_id' => Role::findByName('privacc')->id,
        ]);
    }
}
