<?php

namespace Tests\Unit\Mship;

use App\Events\Mship\Bans\BanUpdated;
use App\Models\Mship\Account;
use App\Models\Mship\Ban\Reason;
use App\Models\Mship\Role;
use App\Notifications\Mship\BanCreated;
use App\Notifications\Mship\BanRepealed;
use App\Services\Mship\BanAccount;
use App\Services\Mship\RepealBan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
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

        Notification::fake();
    }

    /** @test */
    public function itDispatchesEventOnBanSave()
    {
        $reason = factory(Reason::class)->create();

        $ban = $this->account->addBan($reason, 'ExtraReason', 'NoteForBan', $this->staffAccount);

        Event::assertDispatched(BanUpdated::class, function ($event) use ($ban) {
            return $event->ban->id === $ban->id;
        });
    }

    /** @test */
    public function itAppliesLocalBansCorrectlyViaService()
    {
        $reason = factory(Reason::class)->create();

        $service = new BanAccount($this->account, $reason, $this->staffAccount,
            ['ban_internal_note' => 'Testing an internal note.', 'ban_reason_extra' => 'Testing the note to a user.']);

        $service->handle();

        Notification::assertSentTo($this->account, BanCreated::class);

        $this->assertDatabaseHas('mship_account_ban', [
            'account_id' => $this->account->id,
            'reason_id' => $reason->id,
            'reason_extra' => 'Testing the note to a user.',
        ]);
    }

    /** @test */
    public function itRepealsLocalBansCorrectlyViaService()
    {
        $reason = factory(Reason::class)->create();

        $banService = new BanAccount($this->account, $reason, $this->staffAccount,
            ['ban_internal_note' => 'Testing an internal note.', 'ban_reason_extra' => 'Testing the note to a user.']);

        $banService->handle();

        handleService(new RepealBan($banService->getBanInstance()));

        Notification::assertSentTo($this->account, BanRepealed::class);

        $this->assertDatabaseHas('mship_account_ban', [
            'account_id' => $this->account->id,
            'reason_id' => $reason->id,
            'reason_extra' => 'Testing the note to a user.',
            'repealed_at' => now(),
        ]);
    }
}
