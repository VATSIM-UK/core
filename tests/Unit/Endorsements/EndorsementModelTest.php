<?php

namespace Tests\Unit\Endorsements;

use App\Models\Atc\Endorsement;
use App\Models\NetworkData\Atc;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EndorsementModelTest extends TestCase
{
    use DatabaseTransactions;

    private $endorsement;

    protected function setUp(): void
    {
        parent::setUp();

        $this->endorsement = factory(Endorsement::class)->create();
    }

    /** @test */
    public function itCanBeCreated()
    {
        $endorsement = Endorsement::create([
            'name' => 'My First Endorsement'
        ]);

        $this->assertDatabaseHas('endorsements', ['id' => $endorsement->id, 'name' => $endorsement->name]);
    }

    /** @test */
    public function itCanBeAssociatedWithACondition()
    {
        $condition = factory(Endorsement\Condition::class)->make(['endorsement_id' => null]);

        $this->assertCount(0, $this->endorsement->fresh()->conditions);
        $this->endorsement->conditions()->save($condition);
        $this->assertEquals($this->endorsement->id, $condition->fresh()->endorsement_id);
        $this->assertEquals($this->endorsement->fresh()->conditions()->first()->id, $condition->id);
        $this->assertCount(1, $this->endorsement->fresh()->conditions);


        $condition = factory(Endorsement\Condition::class)->make(['endorsement_id' => null]);
        $this->endorsement->conditions()->save($condition);
        $this->assertCount(2, $this->endorsement->fresh()->conditions);
    }

    /** @test */
    public function itCorrectlyReportsMetStatus()
    {
        $condition = factory(Endorsement\Condition::class, 2)->make(['endorsement_id' => $this->endorsement->id, 'required_hours' => 1, 'within_months' => null, 'type' => Endorsement\Condition::TYPE_ON_SINGLE_AIRFIELD]);
        $condition->first()->positions = ['EGKK_%', 'EGGW_GND'];
        $condition->first()->save();

        $condition->last()->positions = ['EGLL_%'];
        $condition->last()->save();

        $this->assertEquals(2, $this->endorsement->conditions()->count());

        factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'EGKK_TWR',
            'minutes_online' => 60
        ]);

        $session = factory(Atc::class)->create([
            'account_id' => $this->user->id,
            'callsign' => 'EGLL_TWR',
            'minutes_online' => 59
        ]);

        $this->assertFalse($this->endorsement->fresh()->conditionsMetForUser($this->user));

        $session->minutes_online = 66;
        $session->save();

        $this->assertTrue($this->endorsement->fresh()->conditionsMetForUser($this->user));
    }

}
