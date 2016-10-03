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
}
