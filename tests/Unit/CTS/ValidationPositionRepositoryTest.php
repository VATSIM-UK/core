<?php

namespace Tests\Unit\CTS;

use App\Models\Cts\Member;
use App\Models\Cts\Validation;
use App\Models\Cts\ValidationPosition;
use App\Repositories\Cts\ValidationPositionRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_can_find_a_position_by_id()
    {
        $position = ValidationPosition::Factory()->create();
        $search = $this->subjectUnderTest->findByPositionId($position->id);

        $this->assertEquals($position->id, $search->id);
    }

    #[Test]
    public function it_can_find_a_position_by_callsign()
    {
        $position = ValidationPosition::Factory()->create([
            'position' => 'Shanwick (EGGX_FSS)',
        ]);

        $search = $this->subjectUnderTest->findByPosition('EGGX_FSS');

        $this->assertEquals($position->id, $search->id);
    }

    #[Test]
    public function it_returns_validated_members()
    {
        $position = ValidationPosition::Factory()->create();

        Validation::factory()->count(10)->create([
            'position_id' => $position->id,
        ]);

        $this->assertCount(10, $position->members);
    }

    #[Test]
    public function it_formats_validated_members()
    {
        $position = ValidationPosition::Factory()->create();
        $member = Member::Factory()->create();

        Validation::Factory()->create([
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
