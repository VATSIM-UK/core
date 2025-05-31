<?php

namespace Tests\Unit\Account\Relationships;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StateTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_adds_a_state()
    {
        $divisionState = \App\Models\Mship\State::findByCode('DIVISION');

        $this->user->fresh()->addState($divisionState, 'EUR', 'GBR');

        $this->assertDatabaseHas('mship_account_state', [
            'account_id' => $this->user->id,
            'state_id' => $divisionState->id,
            'end_at' => null,
        ]);
    }

    #[Test]
    public function it_throws_invalid_exception_when_searching_for_invalid_state()
    {
        $this->expectException(\App\Exceptions\Mship\InvalidStateException::class);

        $this->user->fresh()->hasState($this->user);
    }

    #[Test]
    public function it_deletes_old_permanent_state()
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

    #[Test]
    public function it_deletes_old_permanent_states()
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

    #[Test]
    public function it_keeps_current_temporary_state()
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

    #[Test]
    public function it_deletes_temporary_states_when_delete_all_temps_state_is_added()
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

    #[Test]
    public function it_returns_correct_primary_state_when_only_one_exists()
    {
        $divisionState = \App\Models\Mship\State::findByCode('DIVISION');

        $this->user->fresh()->addState($divisionState, 'EUR', 'GBR');

        $this->assertEquals($divisionState->id, $this->user->fresh()->primary_state->id);
    }

    #[Test]
    public function it_returns_correct_primary_state_when_temporary_overrides()
    {
        $regionState = \App\Models\Mship\State::findByCode('REGION');
        $visitorState = \App\Models\Mship\State::findByCode('VISITING');

        $this->user->fresh()->addState($regionState, 'EUR', 'EUD');
        $this->user->fresh()->addState($visitorState);

        $this->assertEquals($visitorState->id, $this->user->fresh()->primary_state->id);
    }

    #[Test]
    public function it_returns_correct_primary_state_when_temporary_overrides_and_multiple_temporary()
    {
        $regionState = \App\Models\Mship\State::findByCode('REGION');
        $visitorState = \App\Models\Mship\State::findByCode('VISITING');
        $transferringState = \App\Models\Mship\State::findByCode('TRANSFERRING');

        $this->user->fresh()->addState($regionState, 'EUR', 'EUD');
        $this->user->fresh()->addState($visitorState);
        $this->user->fresh()->addState($transferringState);

        $this->assertEquals($transferringState->id, $this->user->fresh()->primary_state->id);
    }

    #[Test]
    public function itRemainsIdempotentWhenTryingToRemoveAStateThatIsntSet()
    {
        $regionState = \App\Models\Mship\State::findByCode('REGION');

        $this->user->fresh()->removeState($regionState);

        $this->assertFalse($this->user->fresh()->states->contains($regionState));
    }
}
