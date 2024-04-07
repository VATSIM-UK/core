<?php

namespace Tests\Feature\Atc;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\NetworkData\Atc;
use App\Models\Roster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GatwickEndorsementTest extends TestCase
{
    use DatabaseTransactions;

    private const ROUTE = 'controllers.endorsements.gatwick_ground';

    public function testItPassesFor55Hours()
    {
        $account = $this->getS1Account();

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
            ->assertViewHas('hoursMet', true);

    }

    public function testItFailsFor30Hours()
    {
        $account = $this->getS1Account();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGPH_DEL',
            'minutes_online' => 25 * 60,
            'facility_type' => Atc::TYPE_DEL,
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
            ->assertViewHas('hoursMet', false);
    }

    public function testItFailsForHoursNonUK()
    {
        $account = $this->getS1Account();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGPH_DEL',
            'minutes_online' => 25 * 60,
            'facility_type' => Atc::TYPE_DEL,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGPH_GND',
            'minutes_online' => 10 * 60,
            'facility_type' => Atc::TYPE_GND,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'LFPG_GND',
            'minutes_online' => 500 * 60,
            'facility_type' => Atc::TYPE_GND,
        ]);

        $this->actingAs($account->fresh())
            ->get(route(self::ROUTE))
            ->assertStatus(200)
            ->assertViewHas('hoursMet', false);
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

        Roster::create(['account_id' => $account->id])->save();

        return $account;
    }
}
