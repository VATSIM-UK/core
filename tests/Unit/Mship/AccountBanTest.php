<?php

namespace Tests\Unit\Mship;

use App\Events\Mship\Bans\BanUpdated;
use App\Models\Mship\Account;
use App\Models\Mship\Ban\Reason;
use App\Models\Mship\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AccountBanTest extends TestCase
{
    use RefreshDatabase;

    /** @var Account */
    protected $account;

    protected $staffAccount;

    public function setUp()
    {
        parent::setUp();

        $this->account = factory(Account::class)->create();

        $this->staffAccount = factory(Account::class)->create();

        $this->staffAccount->roles()->attach(Role::find(1));

        Event::fake();
    }

    /** @test */
    public function itAppliesLocalBansCorrectly()
    {
        $reason = factory(Reason::class)->create();

        $ban = $this->account->addBan($reason, 'Testing an internal note.', 'Testing the note to a user.', $this->account->id);

        Event::assertDispatched(BanUpdated::class, function ($event) use ($ban) {
            return $event->ban->id === $ban->id;
        });

        $this->assertDatabaseHas('mship_account_ban', [
            'account_id' => $this->account->id,
            'reason_id' => $reason->id,
            'reason_extra' => 'Testing an internal note.',
        ]);
    }

    /** @test **/
    public function itRepealsLocalBansCorrectly()
    {
        $reason = factory(Reason::class)->create();

        $ban = $this->account->addBan($reason, 'Testing an internal note.', 'Testing the note to a user.', $this->account->id);

        $ban->repeal();

        Event::assertDispatched(BanUpdated::class, function ($event) use ($ban) {
            return $event->ban->id === $ban->id;
        });

        $this->assertDatabaseHas('mship_account_ban', [
            'account_id' => $this->account->id,
            'reason_id' => $reason->id,
            'reason_extra' => 'Testing an internal note.',
            'repealed_at' => now(),
        ]);
    }
}
