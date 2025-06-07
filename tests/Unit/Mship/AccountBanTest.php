<?php

namespace Tests\Unit\Mship;

use App\Events\Mship\Bans\BanUpdated;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Ban;
use App\Models\Mship\Ban\Reason;
use App\Notifications\Mship\BanCreated;
use App\Notifications\Mship\BanRepealed;
use App\Services\Mship\BanAccount;
use App\Services\Mship\RepealBan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccountBanTest extends TestCase
{
    use DatabaseTransactions;

    /** @var Account */
    protected $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create();

        Event::fake();

        Notification::fake();
    }

    #[Test]
    public function banned_scopes_work()
    {
        $bannedAccount = Account::factory()->has(Ban::factory())->create();
        $activeAcount = Account::factory()->create();

        $this->assertEquals([$bannedAccount->id], Account::banned()->whereIn('id', [$bannedAccount->id, $activeAcount->id])->pluck('id')->all());
        $this->assertEquals([$activeAcount->id], Account::notBanned()->whereIn('id', [$bannedAccount->id, $activeAcount->id])->pluck('id')->all());
    }

    #[Test]
    public function it_dispatches_event_on_ban_save()
    {
        $reason = Reason::factory()->create();

        $ban = $this->account->addBan($reason, 'ExtraReason', 'NoteForBan', $this->privacc);

        Event::assertDispatched(BanUpdated::class, function ($event) use ($ban) {
            return $event->ban->id === $ban->id;
        });
    }

    #[Test]
    public function it_applies_local_bans_correctly_via_service()
    {
        $reason = Reason::factory()->create();

        $service = new BanAccount($this->account, $reason, $this->privacc,
            ['ban_internal_note' => 'Testing an internal note.', 'ban_reason_extra' => 'Testing the note to a user.']);

        $service->handle();

        Notification::assertSentTo($this->account, BanCreated::class);

        $this->assertDatabaseHas('mship_account_ban', [
            'account_id' => $this->account->id,
            'reason_id' => $reason->id,
            'reason_extra' => 'Testing the note to a user.',
        ]);
    }

    #[Test]
    public function it_repeals_local_bans_correctly_via_service()
    {
        $reason = Reason::factory()->create();

        $banService = new BanAccount($this->account, $reason, $this->privacc,
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
