<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\Training;

use App\Models\Atc\Position;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Notifications\DiscordNotificationChannel;
use App\Notifications\Training\TrainingPlaceOfferDeclined;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingPlaceOfferDeclinedNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private Account $account;

    private TrainingPlaceOffer $offer;

    protected function setUp(): void
    {
        parent::setUp();

        $ctsMember = Member::factory()->create();
        $this->account = Account::factory()->create(['id' => $ctsMember->cid]);

        $waitingList = WaitingList::factory()->create(['name' => 'Test Waiting List']);
        $waitingListAccount = $waitingList->addToWaitingList($this->account, Account::factory()->create());

        $position = Position::factory()->create(['name' => 'EGLL Tower', 'callsign' => 'EGLL_TWR']);
        $trainingPosition = TrainingPosition::factory()->create([
            'position_id' => $position->id,
            'cts_positions' => [],
            'training_team_discord_channel_id' => null,
        ]);

        $this->offer = TrainingPlaceOffer::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);
    }

    #[Test]
    public function it_includes_discord_channel_in_via_if_channel_id_is_set(): void
    {
        $this->offer->trainingPosition->update([
            'training_team_discord_channel_id' => '123456789',
        ]);

        $notification = new TrainingPlaceOfferDeclined($this->offer);

        $channels = $notification->via($this->account);
        $this->assertContains(DiscordNotificationChannel::class, $channels);
    }

    #[Test]
    public function it_omits_discord_channel_in_via_if_channel_id_is_null_or_empty(): void
    {
        $notification = new TrainingPlaceOfferDeclined($this->offer);

        $channels = $notification->via($this->account);
        $this->assertNotContains(DiscordNotificationChannel::class, $channels);
    }
}
