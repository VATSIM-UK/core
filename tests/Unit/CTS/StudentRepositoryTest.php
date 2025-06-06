<?php

namespace Tests\Unit\CTS;

use App\Models\Cts\Member;
use App\Models\Cts\Position;
use App\Models\Cts\PositionValidation;
use App\Repositories\Cts\StudentRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_can_return_a_list_of_students_of_an_rts()
    {
        $position = Position::factory()->create(['rts_id' => 15]);

        $positionValidation = factory(PositionValidation::class)->create([
            'status' => 1,
            'position_id' => $position->id,
        ]);

        $students = $this->subjectUnderTest->getStudentsWithin(15);

        $this->assertEquals($students->first(), $positionValidation->member->cid);
    }

    #[Test]
    public function it_does_not_return_students_of_another_rts()
    {
        $position = Position::factory()->create(['rts_id' => 15]);

        factory(PositionValidation::class)->create([
            'status' => 1,
            'position_id' => $position->id,
        ]);

        $students = $this->subjectUnderTest->getStudentsWithin(10);

        $this->assertNull($students->first());
    }

    #[Test]
    public function it_only_returns_a_students_once_within_an_rts()
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

    #[Test]
    public function it_formats_the_return_data_for_an_rts_correctly()
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

    #[Test]
    public function it_does_not_return_mentors_as_students()
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
