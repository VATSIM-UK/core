<?php

namespace Tests\Feature\Atc;

use App\Models\Atc\Endorsement;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AreaHourCheckTest extends TestCase
{
    use DatabaseTransactions;

    protected $hourCheckRoute = 'controllers.hour_check.area';

    /** @test */
    public function testRedirectsAwayIfUserNotS3()
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
    public function testSuccessfulNavigationIfUserIsS3()
    {
        // create relevant endorsement.
        factory(Endorsement::class)->create(['name' => 'LON_S_CTR']);
        $account = Account::factory()->create();

        $qualification = Qualification::code('S3')->first();
        $account->addQualification($qualification);
        $account->save();

        $this->actingAs($account->fresh())
            ->get(route($this->hourCheckRoute))
            ->assertOk();
    }

    /** @test */
    public function testRedirectsAwayIfNoRelevantEndorsementsCreated()
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
