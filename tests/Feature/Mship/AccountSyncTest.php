<?php

namespace Tests\Feature\Mship;

use App\Events\Mship\AccountAltered;
use App\Models\Mship\Account;
use App\Models\Mship\Ban\Reason;
use Carbon\Carbon;
use Faker\Provider\Lorem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AccountSyncTest extends TestCase
{
    use DatabaseTransactions;

    private $account;

    protected function setUp()
    {
        parent::setUp();
        $initialDispatcher = Event::getFacadeRoot();
        Event::fake();
        Model::setEventDispatcher($initialDispatcher);

        $this->account = factory(Account::class)->create();
    }

    /** @test **/
    public function testItTriggersWhenEmailChanged()
    {
        $this->account->email = 'joe@example.org';

        Event::assertDispatched(AccountAltered::class);
    }

    /** @test **/
    public function testItTriggersWhenBanned()
    {
        $reason = factory(Reason::class)->create();
        $banner = factory(Account::class)->create();
        $this->account->addBan($reason, Lorem::paragraph(), Lorem::paragraph(), $banner);

        Event::assertDispatched(AccountAltered::class);
    }

    /** @test **/
    public function testItTriggersWhenUnBanned()
    {
        $ban = factory(Account\Ban::class)->create();
        $ban->repeal();

        Event::assertDispatched(AccountAltered::class);
    }

    /** @test **/
    public function testItDoesntTriggerWhenUntrackedValuesChanged()
    {
        $this->account->last_login = Carbon::now();
        $this->account->updated_at = Carbon::now();
        $this->account->save();

        Event::assertDispatched(AccountAltered::class);
    }
}
