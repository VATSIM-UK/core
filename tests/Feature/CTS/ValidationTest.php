<?php

namespace Tests\Feature\CTS;

use App\Models\Cts\Member;
use App\Models\Cts\Validation;
use App\Models\Cts\ValidationPosition;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itReturns400WhenPositionNotSupplied()
    {
        $this->call('GET', route('api.validations'), ['position' => ''])
            ->assertStatus(400)
            ->assertJsonStructure([
                'status', 'message',
            ]);
    }

    /** @test */
    public function itReturns404WhenPositionDoesNotExist()
    {
        $this->call('GET', route('api.validations'), ['position' => 'EGKK'])
            ->assertStatus(404)
            ->assertJsonStructure([
                'status', 'message',
            ]);
    }

    /** @test */
    public function itReturnsAJsonResponse()
    {
        $member = factory(Member::class)->create();
        $position = factory(ValidationPosition::class)->create();
        $validation = factory(Validation::class)->create([
            'member_id' => $member,
            'position_id' => $position,
        ]);

        $this->call('GET', route('api.validations'), ['position' => $position->position])
            ->assertStatus(200)
            ->assertExactJson([
                'status' => [
                    'position' => $position->position,
                ],
                'validated_members' => [
                    [
                        'id' => $member->cid,
                        'name' => $member->name,
                    ],
                ],
            ]);
    }
}
