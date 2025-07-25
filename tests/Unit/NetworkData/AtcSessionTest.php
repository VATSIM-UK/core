<?php

namespace Tests\Unit\NetworkData;

use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AtcSessionTest extends TestCase
{
    use DatabaseTransactions;

    private $atcSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->atcSession = factory(Atc::class)->create();
    }

    #[Test]
    public function it_returns_a_message_for_a_facility()
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

    #[Test]
    public function it_detects_whether_a_session_is_within_the_uk()
    {
        tap(factory(Atc::class)->create(['callsign' => 'EGGD_APP']), function ($model) {
            $this->assertEquals(true, $model->uk_session);
        });

        tap(factory(Atc::class)->create(['callsign' => 'EISN_CTR']), function ($model) {
            $this->assertEquals(false, $model->uk_session);
        });
    }

    #[Test]
    public function it_only_returns_uk_session_data_on_relationship()
    {
        tap(factory(Atc::class)->create(['callsign' => 'EGGD_APP']), function ($model) {
            factory(Atc::class)->create(['callsign' => 'LFMN_APP', 'account_id' => $model->account_id]);
            $this->assertCount(1, Account::find($model->account_id)->networkDataAtcUk()->get());
        });
    }
}
