<?php

declare(strict_types=1);

namespace Tests\Unit\Command;

use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use App\Models\Mship\State;
use App\Models\Roster;
use App\Notifications\Roster\RemovedFromRoster;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateRosterCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
    }

    #[Test]
    public function it_keeps_a_visitor_who_only_holds_the_shanwick_endorsement_on_the_roster(): void
    {
        $account = $this->makeVisitingController();
        $this->giveEndorsement($account, 'Shanwick Oceanic (EGGX)');

        $this->runRosterUpdate();

        $this->assertDatabaseHas('roster', ['account_id' => $account->id]);
        $this->assertDatabaseMissing('roster_history', ['account_id' => $account->id]);
        Notification::assertNotSentTo($account, RemovedFromRoster::class);
    }

    #[Test]
    public function it_removes_a_visitor_who_holds_endorsements_beyond_shanwick(): void
    {
        $account = $this->makeVisitingController();
        $this->giveEndorsement($account, 'Shanwick Oceanic (EGGX)');
        $this->giveEndorsement($account, 'Heathrow');

        $this->runRosterUpdate();

        $this->assertDatabaseMissing('roster', ['account_id' => $account->id]);
        $this->assertDatabaseHas('roster_history', ['account_id' => $account->id]);
        Notification::assertSentTo($account, RemovedFromRoster::class);
    }

    private function makeVisitingController(): Account
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('VISITING'));

        Roster::create(['account_id' => $account->id]);

        return $account->fresh();
    }

    private function giveEndorsement(Account $account, string $positionGroupName): Endorsement
    {
        return Endorsement::createQuietly([
            'account_id' => $account->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory()->create(['name' => $positionGroupName])->id,
        ]);
    }

    private function runRosterUpdate(): void
    {
        Artisan::call('roster:update', [
            'fromDate' => now()->subMonths(3)->toDateString(),
            'toDate' => now()->toDateString(),
        ]);
    }
}
