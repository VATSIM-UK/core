<?php

namespace Tests\Unit\Command;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupermanCommandTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_reports_to_console_when_successful()
    {
        $this->artisan('grant:superman', ['cid' => $this->user->id])
            ->expectsOutput('Account added to the superman role!');
    }

    /** @test */
    public function it_reports_to_console_when_role_already_found()
    {
        $this->user->assignRole(Role::findByName('privacc'));

        $this->artisan('grant:superman', ['cid' => $this->user->id])
            ->expectsOutput('The specified account already has the "superman" role.');
    }

    /** @test */
    public function it_reports_to_console_when_the_cid_is_not_found()
    {
        $this->artisan('grant:superman', ['cid' => 0000000])
            ->expectsOutput('The specific CID was not found.');
    }

    /** @test */
    public function it_attaches_role_successfully()
    {
        $this->artisan('grant:superman', ['cid' => $this->user->id])
            ->expectsOutput('Account added to the superman role!');

        $this->assertDatabaseHas('mship_account_role', [
            'model_id' => $this->user->id,
            'role_id' => Role::findByName('privacc')->id,
        ]);
    }
}
