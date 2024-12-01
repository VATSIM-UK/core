<?php

namespace Tests\Feature\Atc;

use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AreaHourCheckTest extends TestCase
{
    use DatabaseTransactions;

    protected $hourCheckRoute = 'controllers.hour_check.area';

    /** @test */
    public function test_redirects_away_if_user_not_s3()
    {
        $account = Account::factory()->create();

        $qualification = Qualification::code('S2')->first();
        $account->addQualification($qualification);
        $account->save();

        $this->actingAs($account->fresh())
            ->get(route($this->hourCheckRoute))
            ->assertRedirect(route('mship.manage.dashboard'));
    }

    /** @test */
    public function test_successful_navigation_if_user_is_s3()
    {
        $this->markTestSkipped('Page disabled.');
        // create relevant endorsement.
        factory(PositionGroup::class)->create(['name' => 'LON_S_CTR']);
        $account = Account::factory()->create();

        $qualification = Qualification::code('S3')->first();
        $account->addQualification($qualification);
        $account->save();

        $this->actingAs($account->fresh())
            ->get(route($this->hourCheckRoute))
            ->assertOk();
    }

    /** @test */
    public function test_redirects_away_if_no_relevant_endorsements_created()
    {
        $account = Account::factory()->create();

        $qualification = Qualification::code('S3')->first();
        $account->addQualification($qualification);
        $account->save();

        $this->actingAs($account->fresh())
            ->get(route($this->hourCheckRoute))
            ->assertRedirect(route('mship.manage.dashboard'));
    }
}
