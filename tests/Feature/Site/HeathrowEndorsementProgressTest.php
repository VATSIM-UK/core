<?php

namespace Tests\Feature\Site;

use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\NetworkData\Atc;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HeathrowEndorsementProgressTest extends TestCase
{
    use DatabaseTransactions;

    private const ROUTE = 'site.atc.heathrow';

    #[Test]
    public function test_guest_does_not_see_progress_panel()
    {
        $this->get(route(self::ROUTE))
            ->assertOk()
            ->assertDontSee('Your Progress');
    }

    #[Test]
    public function test_logged_in_user_without_endorsements_sees_gnd_hours()
    {
        $account = $this->createS2Account();
        PositionGroup::factory()->create(['name' => 'Heathrow (GND)']);

        $s1Qual = Qualification::code('S1')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_GND',
            'minutes_online' => 10 * 60,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $s1Qual->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGCC_DEL',
            'minutes_online' => 10 * 60,
            'facility_type' => Atc::TYPE_DEL,
            'qualification_id' => $s1Qual->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGPH_TWR',
            'minutes_online' => 20 * 60,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s1Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('Your Progress')
            ->assertSee('Total UK DEL/GND/TWR')
            ->assertSee('Gatwick DEL/GND/TWR')
            ->assertSee('Manchester DEL/GND/TWR')
            ->assertSee('40 / 40 Hrs')
            ->assertSee('10 / 10 Hrs');
    }

    #[Test]
    public function test_logged_in_user_with_gnd_endorsement_sees_twr_hours()
    {
        $account = $this->createS2Account();
        $gndPg = PositionGroup::factory()->create(['name' => 'Heathrow (GND)']);
        PositionGroup::factory()->create(['name' => 'Heathrow (TWR)']);

        Endorsement::factory()->create([
            'account_id' => $account->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $gndPg->id,
        ]);

        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGLL_GND',
            'minutes_online' => 10 * 60,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $s2Qual->id,
            'connected_at' => Carbon::now()->subMonth(),
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_TWR',
            'minutes_online' => 50 * 60,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s2Qual->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGCC_TWR',
            'minutes_online' => 30 * 60,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s2Qual->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGBB_TWR',
            'minutes_online' => 20 * 60,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s2Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('Your Progress')
            ->assertSee('Heathrow GND/DEL (last 3 months)')
            ->assertSee('Total UK TWR')
            ->assertSee('Manchester TWR')
            ->assertSee('Gatwick TWR')
            ->assertSee('10 / 10 Hrs')
            ->assertSee('100 / 100 Hrs')
            ->assertDontSee('Total UK DEL/GND/TWR');
    }

    #[Test]
    public function test_logged_in_user_with_all_endorsements_sees_no_progress()
    {
        $account = $this->createS2Account();
        $pgIds = [];
        foreach (['Heathrow (GND)', 'Heathrow (TWR)', 'Heathrow (APP)'] as $name) {
            $pg = PositionGroup::factory()->create(['name' => $name]);
            $pgIds[] = $pg->id;
        }

        foreach ($pgIds as $pgId) {
            Endorsement::factory()->create([
                'account_id' => $account->id,
                'endorsable_type' => PositionGroup::class,
                'endorsable_id' => $pgId,
            ]);
        }

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertDontSee('Your Progress');
    }

    #[Test]
    public function test_hours_are_filtered_by_minimum_qualification()
    {
        $account = $this->createS2Account();
        PositionGroup::factory()->create(['name' => 'Heathrow (GND)']);

        $s1Qual = Qualification::code('S1')->firstOrFail();
        $pilotQual = Qualification::factory()->pilot()->create();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_GND',
            'minutes_online' => 10 * 60,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $s1Qual->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGCC_GND',
            'minutes_online' => 100 * 60,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $pilotQual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('10 / 40 Hrs')
            ->assertSee('10 / 10 Hrs');
    }

    private function createS2Account(): Account
    {
        $account = Account::factory()->create();
        $account->addQualification(Qualification::code('S2')->firstOrFail());
        $account->addState(State::findByCode('DIVISION')->firstOrFail(), 'EUR', 'GBR');

        return $account;
    }
}
