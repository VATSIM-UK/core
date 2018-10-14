<?php

namespace Tests\Feature\Atc;

use App\Models\Mship\Account;
use App\Notifications\Atc\TGNCInterest;
use App\Console\Commands\Atc\TGNCInterestCts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TGNCInterestTest extends TestCase
{
    use RefreshDatabase;

    private $validUser;
    private $userNotInArray;

    public function __construct()
    {
        parent::setUp();
        parent::__construct();

        Notification::fake();

        $getUserMock = \Mockery::mock(TGNCInterestCts::class)->shouldReceive('getUsers')->once()->andReturn([1300001, 1300002]);
        $this->app->instance(TGNCInterestCts::class, $getUserMock->getMock());

        $this->validUser = factory(Account::class)->create(['id' => '1300001', 'email' => 'foo@bar.com']);
        $this->userNotInArray = factory(Account::class)->create(['id' => '1300003']);

        $this->artisan('tgnc:interest');
    }

    public function testItSendsANotificationToValidUsers()
    {
        Notification::assertSentTo($this->validUser, TGNCInterest::class);
        Notification::assertNotSentTo($this->userNotInArray, TGNCInterest::class);
    }

    public function testItStoresARecordInTheDatabaseForValidUsers()
    {
        $this->assertDatabaseHas('notifications', [
            'type' => 'App\Notifications\Atc\TGNCInterest',
            'notifiable_id' => $this->validUser->id
        ]);

        $this->assertDatabaseMissing('notifications', [
            'type' => 'App\Notifications\Atc\TGNCInterest',
            'notifiable_id' => $this->userNotInArray->id
        ]);
    }
}
