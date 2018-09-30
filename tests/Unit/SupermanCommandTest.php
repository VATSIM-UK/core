<?php

namespace Tests\Unit;

use App\Models\Mship\Account;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
    public function itAttachesRoleSuccessfully() 
    {
        Artisan::call('grant:superman', ['cid' => $this->account->id]);

        $this->assertDatabaseHas('mship_account_role', [
            'account_id' => $this->account->id,
            'role_id' => 1,
        ]);
    }
}
