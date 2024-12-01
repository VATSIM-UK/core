<?php

namespace Tests\Unit\CTS;

use App\Models\Cts\Member;
use App\Models\Cts\Validation;
use App\Models\Cts\ValidationPosition;
use App\Repositories\Cts\ValidationPositionRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ValidationPositionRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /* @var ValidationPositionRepository */
    protected $subjectUnderTest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subjectUnderTest = resolve(ValidationPositionRepository::class);
    }

    /** @test */
    public function it_can_find_a_position_by_id()
    {
        $position = factory(ValidationPosition::class)->create();
        $search = $this->subjectUnderTest->findByPositionId($position->id);

        $this->assertEquals($position->id, $search->id);
    }

    /** @test */
    public function it_can_find_a_position_by_callsign()
    {
        $position = factory(ValidationPosition::class)->create([
            'position' => 'Shanwick (EGGX_FSS)',
        ]);

        $search = $this->subjectUnderTest->findByPosition('EGGX_FSS');

        $this->assertEquals($position->id, $search->id);
    }

    /** @test */
    public function it_returns_validated_members()
    {
        $position = factory(ValidationPosition::class)->create();

        factory(Validation::class, 10)->create([
            'position_id' => $position->id,
        ]);

        $this->assertCount(10, $position->members);
    }

    /** @test */
    public function it_formats_validated_members()
    {
        $position = factory(ValidationPosition::class)->create();
        $member = factory(Member::class)->create();

        factory(Validation::class)->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
        ]);

        $expected = [
            'id' => $member->cid,
            'name' => $member->name,
        ];

        $this->assertContains($expected, $this->subjectUnderTest->getValidatedMembersFor($position));
    }
}
