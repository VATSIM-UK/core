<?php

namespace Tests\Unit\NetworkData;

use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtcSessionTest extends TestCase
{
    use RefreshDatabase;

    private $atcSession;

    public function setUp()
    {
        parent::setUp();

        $this->atcSession = factory(Atc::class)->create();
    }

    /** @test **/
    public function itReturnsAMessageForAFacility()
    {
        tap(factory(Atc::class)->make(['facility_type' => 1]), function ($model) {
            $this->assertEquals('Observer', $model->type);
        });

        tap(factory(Atc::class)->make(['facility_type' => 2]), function ($model) {
            $this->assertEquals('Delivery', $model->type);
        });

        tap(factory(Atc::class)->make(['facility_type' => 3]), function ($model) {
            $this->assertEquals('Ground', $model->type);
        });

        tap(factory(Atc::class)->make(['facility_type' => 4]), function ($model) {
            $this->assertEquals('Tower', $model->type);
        });

        tap(factory(Atc::class)->make(['facility_type' => 5]), function ($model) {
            $this->assertEquals('Approach', $model->type);
        });

        tap(factory(Atc::class)->make(['facility_type' => 6]), function ($model) {
            $this->assertEquals('En-Route', $model->type);
        });

        tap(factory(Atc::class)->make(['facility_type' => 7]), function ($model) {
            $this->assertEquals('Flight Service Station', $model->type);
        });

        tap(factory(Atc::class)->make(['facility_type' => 8]), function ($model) {
            $this->assertEquals('Unknown', $model->type);
        });
    }

    /** @test **/
    public function itDetectsWhetherASessionIsWithinTheUK()
    {
        tap(factory(Atc::class)->create(['callsign' => 'EGGD_APP']), function ($model) {
            $this->assertEquals(true, $model->uk_session);
        });

        tap(factory(Atc::class)->create(['callsign' => 'EISN_CTR']), function ($model) {
            $this->assertEquals(false, $model->uk_session);
        });
    }

    /** @test **/
    public function itOnlyReturnsUkSessionDataOnRelationship()
    {
        tap(factory(Atc::class)->create(['callsign' => 'EGGD_APP']), function ($model) {
            factory(Atc::class)->create(['callsign' => 'LFMN_APP', 'account_id' => $model->account_id]);
            $this->assertCount(1, Account::find($model->account_id)->networkDataAtcUk()->get());
        });
    }
}
