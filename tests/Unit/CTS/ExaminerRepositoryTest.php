<?php

namespace Tests\Unit\CTS;

use App\Models\Cts\ExaminerSettings;
use App\Models\Cts\Member;
use App\Repositories\Cts\ExaminerRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExaminerRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /* @var ExaminerRepository */
    protected $subjectUnderTest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subjectUnderTest = resolve(ExaminerRepository::class);
    }

    /** @test */
    public function itCanReturnAListOfAtcExaminers()
    {
        $examiner = factory(ExaminerSettings::class)->create([
            'S1' => 1,
        ]);

        $examiners = $this->subjectUnderTest->getAtcExaminers();

        $this->assertEquals($examiners->first(), $examiner->member->cid);
    }

    /** @test */
    public function itCanReturnAListOfPilotExaminers()
    {
        $examiner = factory(ExaminerSettings::class)->create([
            'P1' => 1,
        ]);

        $examiners = $this->subjectUnderTest->getPilotExaminers();

        $this->assertEquals($examiners->first(), $examiner->member->cid);
    }

    /** @test */
    public function itDoesNotReturnAnExaminerIfNotSetAsExaminerOnMembersTable()
    {
        factory(ExaminerSettings::class)->create([
            'memberID' => factory(Member::class)->create(['examiner' => 0]),
            'S1' => 1,
            'P1' => 1,
        ]);

        $atcExaminers = $this->subjectUnderTest->getAtcExaminers();
        $pilotExaminers = $this->subjectUnderTest->getPilotExaminers();

        $this->assertNull($atcExaminers->first());
        $this->assertNull($pilotExaminers->first());
    }
}
