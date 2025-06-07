<?php

namespace Tests\Feature\API;

use App\Models\Atc\Position;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\Roster;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_returns400_when_position_not_supplied()
    {
        $this->call('GET', route('api.validations'), ['position' => ''])
            ->assertStatus(400)
            ->assertJsonStructure([
                'status', 'message',
            ]);
    }

    #[Test]
    public function it_returns404_when_position_does_not_exist()
    {
        $this->call('GET', route('api.validations'), ['position' => 'EGKK'])
            ->assertStatus(404)
            ->assertJsonStructure([
                'status', 'message',
            ]);
    }

    #[Test]
    public function it_returns_a_json_response()
    {
        $qualification = Qualification::code('S2')->first();
        $account = Account::factory()->create();
        $account->addQualification($qualification);
        $account->addState(State::findByCode('DIVISION'));
        $position = Position::factory()->create([
            'type' => Position::TYPE_TOWER,
        ]);
        Roster::create([
            'account_id' => $account->id,
        ]);

        $endorsement = Endorsement::factory()->create([
            'account_id' => $account,
            'endorsable_type' => Position::class,
            'endorsable_id' => $position,
        ]);

        $this->call('GET', route('api.validations'), ['position' => $position->callsign])
            ->assertStatus(200)
            ->assertExactJson([
                'status' => [
                    'position' => $position->name,
                ],
                'validated_members' => [
                    [
                        'id' => $endorsement->account_id,
                    ],
                ],
            ]);
    }
}
