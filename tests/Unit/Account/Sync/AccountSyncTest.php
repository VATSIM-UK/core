<?php

namespace Tests\Unit\Account\Sync;

use App\Events\Mship\AccountAltered;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Ban;
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

    protected function setUp(): void
    {
        parent::setUp();

        // Setup for event faking
        $initialDispatcher = Event::getFacadeRoot();
        Event::fake();
        Model::setEventDispatcher($initialDispatcher);
    }

    /** @test */
    public function itTriggersWhenEmailChanged()
    {
        $this->user->email = 'joe@example.org';

        Event::assertDispatched(AccountAltered::class);
    }

    /** @test */
    public function itTriggersWhenBanned()
    {
        $reason = Reason::factory()->create();
        $banner = Account::factory()->create();

        $this->user->addBan($reason, Lorem::paragraph(), Lorem::paragraph(), $banner);

        Event::assertDispatched(AccountAltered::class);
    }

    /** @test */
    public function itTriggersWhenUnBanned()
    {
        $ban = Ban::factory()->create();
        $ban->repeal();

        Event::assertDispatched(AccountAltered::class);
    }

    /** @test */
    public function itDoesntTriggerWhenUntrackedValuesChanged()
    {
        $this->user->last_login = Carbon::now();
        $this->user->updated_at = Carbon::now();
        $this->user->save();

        Event::assertNotDispatched(AccountAltered::class);
    }
}
