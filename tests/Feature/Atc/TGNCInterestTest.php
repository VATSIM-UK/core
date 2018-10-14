<?php

namespace Tests\Feature\Atc;

use App\Models\Mship\Account;
use App\Notifications\Atc\TGNCInterest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TGNCInterestTest extends TestCase
{
    use RefreshDatabase;

    private $userOne;
    private $userTwo;

    public function __construct()
    {
        parent::setUp();
        parent::__construct();
    }

    public function testItSendsANotificationToValidUsers()
    {
        Notification::fake();

        $validUser = factory(Account::class)->create(['id' => '1300001', 'email' => 'foo@bar.com']);
        $userNotInArray = factory(Account::class)->create(['id' => '1300003']);

        $this->artisan('tgnc:interest');

        Notification::assertSentTo($validUser, TGNCInterest::class);
        Notification::assertNotSentTo($userNotInArray, TGNCInterest::class);
    }
}

/*
 * This function mocks a return that would usually be given
 * to us by CTS via a Stored Procedure.
 */

class TGNCInterestCts
{
    public static function getUsers()
    {
        return [1300001, 1300002];
    }
}
