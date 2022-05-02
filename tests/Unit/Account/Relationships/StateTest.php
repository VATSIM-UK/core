<?php

namespace Tests\Unit\Account\Relationships;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StateTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itAddsAState()
    {
        $divisionState = \App\Models\Mship\State::findByCode('DIVISION');

        $this->user->fresh()->addState($divisionState, 'EUR', 'GBR');

        $this->assertDatabaseHas('mship_account_state', [
            'account_id' => $this->user->id,
            'state_id' => $divisionState->id,
            'end_at' => null,
        ]);
    }

    /** @test */
    public function itThrowsInvalidExceptionWhenSearchingForInvalidState()
    {
        $this->expectException(\App\Exceptions\Mship\InvalidStateException::class);

        $this->user->fresh()->hasState($this->user);
    }

    /** @test */
    public function itDeletesOldPermanentState()
    {
        $divisionState = \App\Models\Mship\State::findByCode('DIVISION');
        $regionState = \App\Models\Mship\State::findByCode('REGION');

        $this->user->fresh()->addState($regionState, 'EUR', 'EUD');

        $this->assertDatabaseHas('mship_account_state', [
            'account_id' => $this->user->id,
            'state_id' => $regionState->id,
            'end_at' => null,
        ]);

        $this->user->fresh()->addState($divisionState, 'EUR', 'GBR');

        $this->assertDatabaseHas('mship_account_state', [
            'account_id' => $this->user->id,
            'state_id' => $divisionState->id,
            'end_at' => null,
        ]);

        $this->assertDatabaseMissing('mship_account_state', [
            'account_id' => $this->user->id,
            'state_id' => $regionState->id,
            'end_at' => null,
        ]);

        $this->assertDatabaseHas('mship_account_state', [
            'account_id' => $this->user->id,
            'state_id' => $regionState->id,
        ]);
    }

    /** @test */
    public function itDeletesOldPermanentStates()
    {
        // Setup
        $regionState = \App\Models\Mship\State::findByCode('REGION');
        $internationalState = \App\Models\Mship\State::findByCode('INTERNATIONAL');
        $visitingState = \App\Models\Mship\State::findByCode('VISITING');
        $this->user->states()->attach($visitingState, [
            'start_at' => Carbon::now(),
        ]);

        $this->insertFiveDuplicatedStates($regionState, 'EUD', 'EUR');

        $this->assertEquals(6, $this->user->fresh()->states()->count());

        // Now add the same state again.
        $this->user->fresh()->addState($regionState, 'EUR', 'EUD');
        $this->assertEquals(1, $this->user->fresh()->states()->permanent()->count());
        $this->assertDatabaseHas('mship_account_state', [
            'account_id' => $this->user->id,
            'state_id' => $regionState->id,
            'region' => 'EUR',
            'division' => 'EUD',
        ]);

        $this->insertFiveDuplicatedStates($regionState, 'EUD', 'EUR');
        $this->user->states()->attach($internationalState, [
            'region' => 'WA',
            'division' => 'ASIA',
            'start_at' => Carbon::now(),
        ]);

        // Now add new state.
        $this->user->fresh()->addState($internationalState, 'USA', 'USA-N');
        $this->assertEquals(1, $this->user->fresh()->states()->permanent()->count());
        $this->assertDatabaseHas('mship_account_state', [
            'account_id' => $this->user->id,
            'state_id' => $internationalState->id,
            'region' => 'USA',
            'division' => 'USA-N',
        ]);
    }

    private function insertFiveDuplicatedStates($state, $region, $division)
    {
        for ($i = 0; $i < 5; $i++) {
            $this->user->states()->attach($state, [
                'start_at' => Carbon::now(),
                'region' => $region,
                'division' => $division,
            ]);
        }
    }

    /** @test */
    public function itKeepsCurrentTemporaryState()
    {
        $visitorState = \App\Models\Mship\State::findByCode('VISITING');
        $regionState = \App\Models\Mship\State::findByCode('REGION');

        $this->user->fresh()->addState($visitorState);

        $this->assertDatabaseHas('mship_account_state', [
            'account_id' => $this->user->id,
            'state_id' => $visitorState->id,
            'end_at' => null,
        ]);

        $this->user->fresh()->fresh()->addState($regionState, 'EUR', 'EUD');

        $this->assertDatabaseHas('mship_account_state', [
            'account_id' => $this->user->id,
            'state_id' => $visitorState->id,
            'end_at' => null,
        ]);

        $this->assertDatabaseHas('mship_account_state', [
            'account_id' => $this->user->id,
            'state_id' => $visitorState->id,
            'end_at' => null,
        ]);
    }

    /** @test */
    public function itDeletesTemporaryStatesWhenDeleteAllTempsStateIsAdded()
    {
        $visitorState = \App\Models\Mship\State::findByCode('VISITING');
        $divisionState = \App\Models\Mship\State::findByCode('DIVISION');

        $this->user->fresh()->addState($visitorState);

        $this->assertDatabaseHas('mship_account_state', [
            'account_id' => $this->user->id,
            'state_id' => $visitorState->id,
            'end_at' => null,
        ]);

        $this->user->fresh()->addState($divisionState, 'EUR', 'GBR');

        $this->assertDatabaseHas('mship_account_state', [
            'account_id' => $this->user->id,
            'state_id' => $divisionState->id,
            'end_at' => null,
        ]);

        $this->assertDatabaseMissing('mship_account_state', [
            'account_id' => $this->user->id,
            'state_id' => $visitorState->id,
            'end_at' => null,
        ]);

        $this->assertDatabaseHas('mship_account_state', [
            'account_id' => $this->user->id,
            'state_id' => $visitorState->id,
        ]);
    }

    /** @test */
    public function itReturnsCorrectPrimaryStateWhenOnlyOneExists()
    {
        $divisionState = \App\Models\Mship\State::findByCode('DIVISION');

        $this->user->fresh()->addState($divisionState, 'EUR', 'GBR');

        $this->assertEquals($divisionState->id, $this->user->fresh()->primary_state->id);
    }

    /** @test */
    public function itReturnsCorrectPrimaryStateWhenTemporaryOverrides()
    {
        $regionState = \App\Models\Mship\State::findByCode('REGION');
        $visitorState = \App\Models\Mship\State::findByCode('VISITING');

        $this->user->fresh()->addState($regionState, 'EUR', 'EUD');
        $this->user->fresh()->addState($visitorState);

        $this->assertEquals($visitorState->id, $this->user->fresh()->primary_state->id);
    }

    /** @test */
    public function itReturnsCorrectPrimaryStateWhenTemporaryOverridesAndMultipleTemporary()
    {
        $regionState = \App\Models\Mship\State::findByCode('REGION');
        $visitorState = \App\Models\Mship\State::findByCode('VISITING');
        $transferringState = \App\Models\Mship\State::findByCode('TRANSFERRING');

        $this->user->fresh()->addState($regionState, 'EUR', 'EUD');
        $this->user->fresh()->addState($visitorState);
        $this->user->fresh()->addState($transferringState);

        $this->assertEquals($transferringState->id, $this->user->fresh()->primary_state->id);
    }

    /* @test */
    public function itRemainsIdempotentWhenTryingToRemoveAStateThatIsntSet()
    {
        $regionState = \App\Models\Mship\State::findByCode('REGION');

        $this->user->fresh()->removeState($regionState);

        $this->assertFalse($this->user->fresh()->states->contains($regionState));
    }
}
