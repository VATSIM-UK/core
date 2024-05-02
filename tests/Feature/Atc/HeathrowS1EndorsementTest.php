<?php

namespace Tests\Feature\Atc;

use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\NetworkData\Atc;
use App\Models\Roster;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HeathrowS1EndorsementTest extends TestCase
{
    use DatabaseTransactions;

    private const ROUTE = 'controllers.endorsements.heathrow_ground_s1';

    public function testItPassesFor55Hours()
    {
        $account = $this->getS1Account();
        $this->endorseForEgkk($account, Carbon::create(2000, 1, 1));

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_DEL',
            'minutes_online' => 10 * 60,
            'facility_type' => Atc::TYPE_DEL,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_GND',
            'minutes_online' => 25 * 60,
            'facility_type' => Atc::TYPE_GND,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK__GND',
            'minutes_online' => 30 * 60,
            'facility_type' => Atc::TYPE_GND,
        ]);

        $this->actingAs($account->fresh())
            ->get(route(self::ROUTE))
            ->assertStatus(200)
            ->assertViewHas('hoursMet', true)
            ->assertViewHas('hasEgkkEndorsement', true)
            ->assertViewHas('onRoster', true)
            ->assertViewHas('conditionsMet', true);
    }

    public function testItFailsFor55HoursNonGatwick()
    {
        $account = $this->getS1Account();
        $this->endorseForEgkk($account, Carbon::create(2000, 1, 1));

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGPH_DEL',
            'minutes_online' => 25 * 60,
            'facility_type' => Atc::TYPE_DEL,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGCC_GND',
            'minutes_online' => 30 * 60,
            'facility_type' => Atc::TYPE_GND,
        ]);

        $this->actingAs($account->fresh())
            ->get(route(self::ROUTE))
            ->assertStatus(200)
            ->assertViewHas('hoursMet', false)
            ->assertViewHas('onRoster', true)
            ->assertViewHas('hasEgkkEndorsement', true)
            ->assertViewHas('conditionsMet', false);
    }

    public function testItFailsFor55HoursPreEndorsementGatwick()
    {
        $account = $this->getS1Account();
        $this->endorseForEgkk($account, Carbon::create(2025, 1, 1));

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_T_GND',
            'minutes_online' => 55 * 60,
            'facility_type' => Atc::TYPE_DEL,
            'connected_at' => Carbon::create(2024, 1, 1),
        ]);

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_GND',
            'minutes_online' => 55 * 60,
            'facility_type' => Atc::TYPE_DEL,
            'connected_at' => Carbon::create(2024, 2, 1),
        ]);

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_GND',
            'minutes_online' => 25 * 60,
            'facility_type' => Atc::TYPE_GND,
            'connected_at' => Carbon::create(2026, 1, 1),
        ]);

        $this->actingAs($account->fresh())
            ->get(route(self::ROUTE))
            ->assertStatus(200)
            ->assertViewHas('hoursMet', false)
            ->assertViewHas('progress', 50.0)
            ->assertViewHas('onRoster', true)
            ->assertViewHas('hasEgkkEndorsement', true)
            ->assertViewHas('conditionsMet', false);
    }

    public function testItDetectsNotOnRoster()
    {
        $account = $this->getS1AccountNotOnRoster();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGPH_DEL',
            'minutes_online' => 25 * 60,
            'facility_type' => Atc::TYPE_DEL,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGPH_GND',
            'minutes_online' => 25 * 60,
            'facility_type' => Atc::TYPE_GND,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGCC_GND',
            'minutes_online' => 5 * 60,
            'facility_type' => Atc::TYPE_GND,
        ]);

        $this->actingAs($account->fresh())
            ->get(route(self::ROUTE))
            ->assertStatus(200)
            ->assertViewHas('onRoster', false)
            ->assertViewHas('conditionsMet', false);
    }

    public function testItRedirectsForNonS1()
    {
        $account = Account::factory()->create();

        $this->actingAs($account->fresh())
            ->get(route(self::ROUTE))
            ->assertRedirect(route('mship.manage.dashboard'));
    }

    public function testItRedirectsForS2()
    {
        $account = Account::factory()->create();

        $qualification = Qualification::code('S2')->first();
        $account->addQualification($qualification);
        $account->save();

        $this->actingAs($account->fresh())
            ->get(route(self::ROUTE))
            ->assertRedirect(route('mship.manage.dashboard'));
    }

    private function getS1Account(): Account
    {
        $account = Account::factory()->create();

        $qualification = Qualification::code('S1')->first();
        $account->addQualification($qualification);
        $account->save();

        $divisionState = State::findByCode('DIVISION')->firstOrFail();
        $account->addState($divisionState, 'EUR', 'GBR');
        Roster::create(['account_id' => $account->id])->save();

        return $account;
    }

    public function endorseForEgkk(Account $account, Carbon $from): void
    {
        $positionGroup = PositionGroup::where('name', 'Gatwick S1 (DEL/GND)')->firstOrFail();
        Account\Endorsement::create([
            'account_id' => $account->id,
            'endorsable_id' => $positionGroup->id,
            'endorsable_type' => PositionGroup::class,
            'created_at' => $from,
        ]);
    }

    private function getS1AccountNotOnRoster(): Account
    {
        $account = Account::factory()->create();

        $qualification = Qualification::code('S1')->first();
        $account->addQualification($qualification);
        $account->save();

        $divisionState = State::findByCode('DIVISION')->firstOrFail();
        $account->addState($divisionState, 'EUR', 'GBR');

        return $account;
    }
}
