<?php

namespace Tests\Unit\CTS;

use App\Models\Cts\Member;
use App\Models\Cts\Position;
use App\Models\Cts\PositionValidation;
use App\Repositories\Cts\StudentRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StudentRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /* @var StudentRepository */
    protected $subjectUnderTest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subjectUnderTest = resolve(StudentRepository::class);
    }

    /** @test */
    public function itCanReturnAListOfStudentsOfAnRts()
    {
        $position = Position::factory()->create(['rts_id' => 15]);

        $positionValidation = factory(PositionValidation::class)->create([
            'status' => 1,
            'position_id' => $position->id,
        ]);

        $students = $this->subjectUnderTest->getStudentsWithin(15);

        $this->assertEquals($students->first(), $positionValidation->member->cid);
    }

    /** @test */
    public function itDoesNotReturnStudentsOfAnotherRts()
    {
        $position = Position::factory()->create(['rts_id' => 15]);

        factory(PositionValidation::class)->create([
            'status' => 1,
            'position_id' => $position->id,
        ]);

        $students = $this->subjectUnderTest->getStudentsWithin(10);

        $this->assertNull($students->first());
    }

    /** @test */
    public function itOnlyReturnsAStudentsOnceWithinAnRts()
    {
        $member = factory(Member::class)->create();

        $positionOne = Position::factory()->create(['rts_id' => 15]);
        $positionTwo = Position::factory()->create(['rts_id' => 15]);

        factory(PositionValidation::class)->create([
            'member_id' => $member->id,
            'status' => 1,
            'position_id' => $positionOne->id,
        ]);

        factory(PositionValidation::class)->create([
            'member_id' => $member->id,
            'status' => 1,
            'position_id' => $positionTwo->id,
        ]);

        $rts = $this->subjectUnderTest->getStudentsWithin(15);

        $this->assertCount(1, $rts);
    }

    /** @test */
    public function itFormatsTheReturnDataForAnRtsCorrectly()
    {
        $member = factory(Member::class)->create();
        $position = Position::factory()->create(['rts_id' => 15]);

        factory(PositionValidation::class)->create([
            'member_id' => $member->id,
            'status' => 1,
            'position_id' => $position->id,
        ]);

        $return = $this->subjectUnderTest->getStudentsWithin(15);

        $this->assertEquals($return, collect($member->cid));
    }

    /** @test */
    public function itDoesNotReturnMentorsAsStudents()
    {
        $position = Position::factory()->create(['rts_id' => 15]);

        factory(PositionValidation::class)->create([
            'status' => 5,
            'position_id' => $position->id,
        ]);

        $return = $this->subjectUnderTest->getStudentsWithin(15);

        $this->assertNull($return->first());
    }
}
