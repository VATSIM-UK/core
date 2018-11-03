<?php

namespace Tests\Unit;

use App\Models\Mship\Account;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupermanCommandTest extends TestCase
{
    use RefreshDatabase;

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
        $this->account->assignRole(Role::findById(1));

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
            'account_id' => $this->account->id,
            'role_id' => 1,
        ]);
    }
}
