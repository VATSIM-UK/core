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
    public function test_obs_user_does_not_see_any_progress()
    {
        $account = $this->createAccountWithQualification('OBS');
        PositionGroup::factory()->create(['name' => 'Heathrow (GND)']);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertDontSee('Your Progress');
    }

    #[Test]
    public function test_s1_user_does_not_see_any_progress()
    {
        $account = $this->createAccountWithQualification('S1');
        PositionGroup::factory()->create(['name' => 'Heathrow (GND)']);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertDontSee('Your Progress');
    }

    #[Test]
    public function test_s2_user_without_any_endorsements_sees_gnd_bars()
    {
        $account = $this->createAccountWithQualification('S2');
        PositionGroup::factory()->create(['name' => 'Heathrow (GND)']);

        $s2Qual = Qualification::code('S2')->firstOrFail();
        $this->seedGndSessions($account, $s2Qual);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('Your Progress')
            ->assertSee('Total UK DEL/GND/TWR')
            ->assertSee('Gatwick DEL/GND/TWR')
            ->assertSee('Manchester DEL/GND/TWR')
            ->assertDontSee('Total UK TWR')
            ->assertDontSee('Total UK APP');
    }

    #[Test]
    public function test_s2_user_with_gnd_endorsement_sees_twr_bars()
    {
        $account = $this->createAccountWithQualification('S2');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);

        $s2Qual = Qualification::code('S2')->firstOrFail();
        $this->seedTwrSessions($account, $s2Qual);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('Your Progress')
            ->assertSee('Heathrow GND/DEL (last 3 months)')
            ->assertSee('Total UK TWR')
            ->assertSee('Manchester TWR')
            ->assertSee('Gatwick TWR')
            ->assertDontSee('Total UK DEL/GND/TWR')
            ->assertDontSee('Total UK APP');
    }

    #[Test]
    public function test_s3_user_with_gnd_and_twr_endorsements_sees_app_bars()
    {
        $account = $this->createAccountWithQualification('S3');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)', 'Heathrow (APP)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (TWR)']);

        $s3Qual = Qualification::code('S3')->firstOrFail();
        $this->seedAppSessions($account, $s3Qual);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('Your Progress')
            ->assertSee('Heathrow APP (S3+)')
            ->assertSee('Heathrow TWR (last 3 months)')
            ->assertSee('Total UK APP')
            ->assertSee('Manchester APP')
            ->assertSee('Gatwick APP')
            ->assertDontSee('Total UK DEL/GND/TWR')
            ->assertDontSee('Total UK TWR');
    }

    #[Test]
    public function test_user_with_all_heathrow_endorsements_sees_no_progress()
    {
        $account = $this->createAccountWithQualification('S3');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)', 'Heathrow (APP)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (TWR)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (APP)']);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertDontSee('Your Progress');
    }

    #[Test]
    public function test_s3_user_without_any_endorsements_starts_at_gnd()
    {
        $account = $this->createAccountWithQualification('S3');
        PositionGroup::factory()->create(['name' => 'Heathrow (GND)']);

        $s3Qual = Qualification::code('S3')->firstOrFail();
        $this->seedGndSessions($account, $s3Qual);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('Total UK DEL/GND/TWR')
            ->assertDontSee('Total UK TWR');
    }

    #[Test]
    public function test_s2_user_without_gnd_endorsement_cannot_skip_to_twr()
    {
        $account = $this->createAccountWithQualification('S2');
        $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)']);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('Total UK DEL/GND/TWR')
            ->assertDontSee('Total UK TWR');
    }

    #[Test]
    public function test_s3_user_with_gnd_but_not_twr_endorsement_sees_twr_not_app()
    {
        $account = $this->createAccountWithQualification('S3');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)', 'Heathrow (APP)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);

        $s3Qual = Qualification::code('S3')->firstOrFail();
        $this->seedTwrSessions($account, $s3Qual);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('Total UK TWR')
            ->assertDontSee('Total UK DEL/GND/TWR')
            ->assertDontSee('Total UK APP');
    }

    #[Test]
    public function test_s2_user_with_all_heathrow_endorsements_sees_no_progress()
    {
        $account = $this->createAccountWithQualification('S2');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)', 'Heathrow (APP)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (TWR)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (APP)']);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertDontSee('Your Progress');
    }

    #[Test]
    public function test_gnd_total_bar_includes_uk_del_gnd_and_twr()
    {
        $account = $this->createAccountWithQualification('S2');
        PositionGroup::factory()->create(['name' => 'Heathrow (GND)']);
        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_DEL',
            'minutes_online' => 600,
            'facility_type' => Atc::TYPE_DEL,
            'qualification_id' => $s2Qual->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGCC_GND',
            'minutes_online' => 600,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $s2Qual->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGPH_TWR',
            'minutes_online' => 600,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s2Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('30 / 40 Hrs');
    }

    #[Test]
    public function test_gnd_total_bar_excludes_app_and_ctr_facility_types()
    {
        $account = $this->createAccountWithQualification('S2');
        PositionGroup::factory()->create(['name' => 'Heathrow (GND)']);
        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_APP',
            'minutes_online' => 9999,
            'facility_type' => Atc::TYPE_APP,
            'qualification_id' => $s2Qual->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_CTR',
            'minutes_online' => 9999,
            'facility_type' => Atc::TYPE_CTR,
            'qualification_id' => $s2Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('0 / 40 Hrs');
    }

    #[Test]
    public function test_gnd_gatwick_bar_only_counts_egkk_callsigns()
    {
        $account = $this->createAccountWithQualification('S2');
        PositionGroup::factory()->create(['name' => 'Heathrow (GND)']);
        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_GND',
            'minutes_online' => 600,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $s2Qual->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGCC_GND',
            'minutes_online' => 600,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $s2Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('10 / 10 Hrs');
    }

    #[Test]
    public function test_gnd_manchester_bar_only_counts_egcc_callsigns()
    {
        $account = $this->createAccountWithQualification('S2');
        PositionGroup::factory()->create(['name' => 'Heathrow (GND)']);
        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGCC_GND',
            'minutes_online' => 600,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $s2Qual->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_GND',
            'minutes_online' => 600,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $s2Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('10 / 10 Hrs');
    }

    #[Test]
    public function test_gnd_bars_exclude_non_uk_positions()
    {
        $account = $this->createAccountWithQualification('S2');
        PositionGroup::factory()->create(['name' => 'Heathrow (GND)']);
        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'LFPG_GND',
            'minutes_online' => 9999,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $s2Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('0 / 40 Hrs')
            ->assertSee('0 / 10 Hrs');
    }

    #[Test]
    public function test_twr_total_bar_includes_all_uk_twr()
    {
        $account = $this->createAccountWithQualification('S2');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_TWR',
            'minutes_online' => 6000,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s2Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('100 / 100 Hrs');
    }

    #[Test]
    public function test_twr_total_bar_excludes_non_twr_facility_types()
    {
        $account = $this->createAccountWithQualification('S2');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_GND',
            'minutes_online' => 9999,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $s2Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('0 / 100 Hrs');
    }

    #[Test]
    public function test_twr_gatwick_bar_only_counts_egkk_twr()
    {
        $account = $this->createAccountWithQualification('S2');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_TWR',
            'minutes_online' => 1800,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s2Qual->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGCC_TWR',
            'minutes_online' => 9999,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s2Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('30 / 30 Hrs');
    }

    #[Test]
    public function test_twr_manchester_bar_only_counts_egcc_twr()
    {
        $account = $this->createAccountWithQualification('S2');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGCC_TWR',
            'minutes_online' => 1800,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s2Qual->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_TWR',
            'minutes_online' => 9999,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s2Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('30 / 30 Hrs');
    }

    #[Test]
    public function test_twr_heathrow_gnd_del_recent_bar_only_counts_egll_gnd_del_in_last_3_months()
    {
        $account = $this->createAccountWithQualification('S2');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGLL_GND',
            'minutes_online' => 600,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $s2Qual->id,
            'connected_at' => Carbon::now()->subMonth(),
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGLL_DEL',
            'minutes_online' => 600,
            'facility_type' => Atc::TYPE_DEL,
            'qualification_id' => $s2Qual->id,
            'connected_at' => Carbon::now()->subMonth(),
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGLL_TWR',
            'minutes_online' => 9999,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s2Qual->id,
            'connected_at' => Carbon::now()->subMonth(),
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGLL_GND',
            'minutes_online' => 9999,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $s2Qual->id,
            'connected_at' => Carbon::now()->subMonths(6),
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('20 / 10 Hrs');
    }

    #[Test]
    public function test_twr_total_bar_excludes_non_uk_twr()
    {
        $account = $this->createAccountWithQualification('S2');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'LFPG_TWR',
            'minutes_online' => 9999,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s2Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('0 / 100 Hrs');
    }

    #[Test]
    public function test_app_total_bar_includes_all_uk_app()
    {
        $account = $this->createAccountWithQualification('S3');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)', 'Heathrow (APP)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (TWR)']);
        $s3Qual = Qualification::code('S3')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_APP',
            'minutes_online' => 7200,
            'facility_type' => Atc::TYPE_APP,
            'qualification_id' => $s3Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('120 / 120 Hrs');
    }

    #[Test]
    public function test_app_gatwick_bar_only_counts_egkk_app()
    {
        $account = $this->createAccountWithQualification('S3');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)', 'Heathrow (APP)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (TWR)']);
        $s3Qual = Qualification::code('S3')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_APP',
            'minutes_online' => 1800,
            'facility_type' => Atc::TYPE_APP,
            'qualification_id' => $s3Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('30 / 30 Hrs');
    }

    #[Test]
    public function test_app_manchester_bar_only_counts_egcc_app()
    {
        $account = $this->createAccountWithQualification('S3');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)', 'Heathrow (APP)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (TWR)']);
        $s3Qual = Qualification::code('S3')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGCC_APP',
            'minutes_online' => 1800,
            'facility_type' => Atc::TYPE_APP,
            'qualification_id' => $s3Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('30 / 30 Hrs');
    }

    #[Test]
    public function test_app_heathrow_twr_recent_bar_only_counts_egll_twr_in_last_3_months()
    {
        $account = $this->createAccountWithQualification('S3');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)', 'Heathrow (APP)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (TWR)']);
        $s3Qual = Qualification::code('S3')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGLL_TWR',
            'minutes_online' => 600,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s3Qual->id,
            'connected_at' => Carbon::now()->subMonth(),
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGLL_GND',
            'minutes_online' => 9999,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $s3Qual->id,
            'connected_at' => Carbon::now()->subMonth(),
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGLL_TWR',
            'minutes_online' => 9999,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s3Qual->id,
            'connected_at' => Carbon::now()->subMonths(6),
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('10 / 10 Hrs');
    }

    #[Test]
    public function test_app_total_bar_excludes_non_uk_app()
    {
        $account = $this->createAccountWithQualification('S3');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)', 'Heathrow (APP)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (TWR)']);
        $s3Qual = Qualification::code('S3')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'LFPG_APP',
            'minutes_online' => 9999,
            'facility_type' => Atc::TYPE_APP,
            'qualification_id' => $s3Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('0 / 120 Hrs');
    }

    #[Test]
    public function test_s1_sessions_are_excluded_from_gnd_hours()
    {
        $account = $this->createAccountWithQualification('S2');
        PositionGroup::factory()->create(['name' => 'Heathrow (GND)']);
        $s1Qual = Qualification::code('S1')->firstOrFail();
        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_GND',
            'minutes_online' => 600,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $s1Qual->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGCC_GND',
            'minutes_online' => 2400,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $s2Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('40 / 40 Hrs')
            ->assertSee('0 / 10 Hrs');
    }

    #[Test]
    public function test_s2_sessions_are_excluded_from_app_hours()
    {
        $account = $this->createAccountWithQualification('S3');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)', 'Heathrow (APP)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (TWR)']);
        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_APP',
            'minutes_online' => 9999,
            'facility_type' => Atc::TYPE_APP,
            'qualification_id' => $s2Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('0 / 120 Hrs');
    }

    #[Test]
    public function test_s3_sessions_are_counted_in_app_hours()
    {
        $account = $this->createAccountWithQualification('S3');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)', 'Heathrow (APP)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (TWR)']);
        $s3Qual = Qualification::code('S3')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_APP',
            'minutes_online' => 7200,
            'facility_type' => Atc::TYPE_APP,
            'qualification_id' => $s3Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('120 / 120 Hrs');
    }

    #[Test]
    public function test_c1_sessions_are_counted_in_gnd_and_app_hours()
    {
        $account = $this->createAccountWithQualification('C1');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)', 'Heathrow (APP)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (TWR)']);
        $c1Qual = Qualification::code('C1')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_APP',
            'minutes_online' => 7200,
            'facility_type' => Atc::TYPE_APP,
            'qualification_id' => $c1Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('120 / 120 Hrs');
    }

    #[Test]
    public function test_sessions_with_zero_qualification_are_excluded()
    {
        $account = $this->createAccountWithQualification('S2');
        PositionGroup::factory()->create(['name' => 'Heathrow (GND)']);

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_GND',
            'minutes_online' => 2400,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => 0,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('0 / 40 Hrs')
            ->assertSee('0 / 10 Hrs');
    }

    #[Test]
    public function test_i_twr_callsigns_are_excluded_from_twr_bars()
    {
        $account = $this->createAccountWithQualification('S2');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_I_TWR',
            'minutes_online' => 9999,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s2Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('0 / 100 Hrs');
    }

    #[Test]
    public function test_i_double_underscore_twr_callsigns_are_excluded()
    {
        $account = $this->createAccountWithQualification('S2');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_I__TWR',
            'minutes_online' => 9999,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s2Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('0 / 100 Hrs');
    }

    #[Test]
    public function test_r_twr_callsigns_are_excluded_from_twr_bars()
    {
        $account = $this->createAccountWithQualification('S2');
        $positionGroups = $this->createPositionGroups(['Heathrow (GND)', 'Heathrow (TWR)']);
        $this->createEndorsement($account, $positionGroups['Heathrow (GND)']);
        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_R_TWR',
            'minutes_online' => 9999,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s2Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('0 / 100 Hrs');
    }

    #[Test]
    public function test_afis_twr_callsigns_are_also_excluded_from_gnd_total_bars()
    {
        $account = $this->createAccountWithQualification('S2');
        PositionGroup::factory()->create(['name' => 'Heathrow (GND)']);
        $s2Qual = Qualification::code('S2')->firstOrFail();

        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_I_TWR',
            'minutes_online' => 9999,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $s2Qual->id,
        ]);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('0 / 40 Hrs');
    }

    #[Test]
    public function test_zero_hours_shown_when_no_atc_sessions_exist()
    {
        $account = $this->createAccountWithQualification('S2');
        PositionGroup::factory()->create(['name' => 'Heathrow (GND)']);

        $this->actingAs($account)
            ->get(route(self::ROUTE))
            ->assertOk()
            ->assertSee('0 / 40 Hrs')
            ->assertSee('0 / 10 Hrs');
    }

    private function createAccountWithQualification(string $code): Account
    {
        $account = Account::factory()->create();
        $account->addQualification(Qualification::code($code)->firstOrFail());
        $account->addState(State::findByCode('DIVISION')->firstOrFail(), 'EUR', 'GBR');

        return $account;
    }

    private function createPositionGroups(array $names): array
    {
        $groups = [];
        foreach ($names as $name) {
            $groups[$name] = PositionGroup::factory()->create(['name' => $name]);
        }

        return $groups;
    }

    private function createEndorsement(Account $account, PositionGroup $positionGroup): Endorsement
    {
        return Endorsement::factory()->create([
            'account_id' => $account->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $positionGroup->id,
        ]);
    }

    private function seedGndSessions(Account $account, Qualification $qualification): void
    {
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_GND',
            'minutes_online' => 600,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $qualification->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGCC_DEL',
            'minutes_online' => 600,
            'facility_type' => Atc::TYPE_DEL,
            'qualification_id' => $qualification->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGPH_TWR',
            'minutes_online' => 1200,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $qualification->id,
        ]);
    }

    private function seedTwrSessions(Account $account, Qualification $qualification): void
    {
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGLL_GND',
            'minutes_online' => 600,
            'facility_type' => Atc::TYPE_GND,
            'qualification_id' => $qualification->id,
            'connected_at' => Carbon::now()->subMonth(),
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_TWR',
            'minutes_online' => 3000,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $qualification->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGCC_TWR',
            'minutes_online' => 1800,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $qualification->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGBB_TWR',
            'minutes_online' => 1200,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $qualification->id,
        ]);
    }

    private function seedAppSessions(Account $account, Qualification $qualification): void
    {
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGLL_TWR',
            'minutes_online' => 600,
            'facility_type' => Atc::TYPE_TWR,
            'qualification_id' => $qualification->id,
            'connected_at' => Carbon::now()->subMonth(),
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_APP',
            'minutes_online' => 3600,
            'facility_type' => Atc::TYPE_APP,
            'qualification_id' => $qualification->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGCC_APP',
            'minutes_online' => 1800,
            'facility_type' => Atc::TYPE_APP,
            'qualification_id' => $qualification->id,
        ]);
        factory(Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGBB_APP',
            'minutes_online' => 1800,
            'facility_type' => Atc::TYPE_APP,
            'qualification_id' => $qualification->id,
        ]);
    }
}
