<?php

namespace Tests\Unit\Mship;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Services\Training\ManualAtcUpgradeService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ManualAtcUpgradeServiceTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_returns_obs_as_next_atc_rating_when_user_has_no_atc_quals()
    {
        $account = Account::factory()->create();

        $obs = Qualification::code('OBS')->first();

        $next = ManualAtcUpgradeService::getNextAtcQualification($account);

        $this->assertNotNull($next);
        $this->assertEquals($obs->id, $next->id);
    }

    #[Test]
    public function it_returns_next_atc_rating_based_on_current_max_vatsim_value()
    {
        $account = Account::factory()->create();

        $obs = Qualification::code('OBS')->first();
        $s1 = Qualification::code('S1')->first();

        // Give the account OBS, so next should be S1
        $account->addQualification($obs);

        $next = ManualAtcUpgradeService::getNextAtcQualification($account);

        $this->assertNotNull($next);
        $this->assertEquals($s1->id, $next->id);
    }

    #[Test]
    public function it_awards_next_atc_rating_and_sets_awarded_date_on_mship_account_qualification_pivot()
    {
        $account = Account::factory()->create();
        $writer = Account::factory()->create();

        $obs = Qualification::code('OBS')->first();
        $awardedOn = CarbonImmutable::parse('2026-01-01')->startOfDay();

        $awarded = ManualAtcUpgradeService::awardNextAtcQualification($account, $awardedOn, $writer->id);

        $this->assertNotNull($awarded);
        $this->assertEquals($obs->id, $awarded->id);

        $this->assertDatabaseHas('mship_account_qualification', [
            'account_id' => $account->id,
            'qualification_id' => $obs->id,
            'created_at' => $awardedOn->toDateTimeString(),
            'updated_at' => $awardedOn->toDateTimeString(),
        ]);

        $this->assertTrue($account->qualifications_atc->contains('id', $obs->id));
    }

    #[Test]
    public function it_returns_null_when_no_next_atc_rating_exists()
    {
        $account = Account::factory()->create();
        $writer = Account::factory()->create();

        $obs = Qualification::code('C3')->first();

        // Give account C3 so there is no higher controller rating
        $account->addQualification($obs);

        $awardedOn = CarbonImmutable::parse('2026-01-01')->startOfDay();
        $result = ManualAtcUpgradeService::awardNextAtcQualification($account, $awardedOn, $writer->id);

        $this->assertNull($result);
    }

    #[Test]
    public function it_detects_administrative_rating_when_account_has_a_qual_with_type_training_atc_or_admin()
    {
        $account = Account::factory()->create();

        $this->assertFalse(ManualAtcUpgradeService::hasAdministrativeRating($account));

        // Give account I1 so we can test if it detects an admininstative rating
        $I1 = Qualification::code('I1')->first();
        $account->addQualification($I1);

        $this->assertTrue(ManualAtcUpgradeService::hasAdministrativeRating($account));
    }
}
