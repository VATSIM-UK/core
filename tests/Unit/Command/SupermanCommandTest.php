<?php

namespace Tests\Unit\Command;

use Laravel\BrowserKitTesting\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupermanCommandTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itReportsToConsoleWhenSuccessful()
    {
        $this->artisan('grant:superman', ['cid' => $this->user->id])
            ->expectsOutput('Account added to the superman role!');
    }

    /** @test */
    public function itReportsToConsoleWhenRoleAlreadyFound()
    {
        $this->user->assignRole(Role::findByName('privacc'));

        $this->artisan('grant:superman', ['cid' => $this->user->id])
            ->expectsOutput('The specified account already has the "superman" role.');
    }

    /** @test */
    public function itReportsToConsoleWhenTheCIDIsNotFound()
    {
        $this->artisan('grant:superman', ['cid' => 0000000])
            ->expectsOutput('The specific CID was not found.');
    }

    /** @test */
    public function itAttachesRoleSuccessfully()
    {
        $this->artisan('grant:superman', ['cid' => $this->user->id])
            ->expectsOutput('Account added to the superman role!');

        $this->assertDatabaseHas('mship_account_role', [
            'model_id' => $this->user->id,
            'role_id' => Role::findByName('privacc')->id,
        ]);
    }
}
