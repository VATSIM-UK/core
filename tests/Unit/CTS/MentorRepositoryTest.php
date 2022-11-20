<?php

namespace Tests\Unit\CTS;

use App\Models\Cts\Member;
use App\Models\Cts\Position;
use App\Models\Cts\PositionValidation;
use App\Repositories\Cts\MentorRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MentorRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /* @var MentorRepository */
    protected $subjectUnderTest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subjectUnderTest = resolve(MentorRepository::class);
    }

    /** @test */
    public function itCanReturnAListOfMentorsOfAnRts()
    {
        $position = factory(Position::class)->create(['rts_id' => 15]);

        $positionValidation = factory(PositionValidation::class)->create([
            'status' => 5,
            'position_id' => $position->id,
        ]);

        $mentors = $this->subjectUnderTest->getMentorsWithin(15);

        $this->assertEquals($mentors->first(), $positionValidation->member->cid);
    }

    /** @test */
    public function itDoesNotReturnMentorsOfAnotherRts()
    {
        $position = factory(Position::class)->create(['rts_id' => 15]);

        factory(PositionValidation::class)->create([
            'status' => 5,
            'position_id' => $position->id,
        ]);

        $mentors = $this->subjectUnderTest->getMentorsWithin(10);

        $this->assertNull($mentors->first());
    }

    /** @test */
    public function itOnlyReturnsAMentorOnceWithinAnRts()
    {
        $member = factory(Member::class)->create();

        $positionOne = factory(Position::class)->create(['rts_id' => 15]);
        $positionTwo = factory(Position::class)->create(['rts_id' => 15]);

        factory(PositionValidation::class)->create([
            'member_id' => $member->id,
            'status' => 5,
            'position_id' => $positionOne->id,
        ]);

        factory(PositionValidation::class)->create([
            'member_id' => $member->id,
            'status' => 5,
            'position_id' => $positionTwo->id,
        ]);

        $rts = $this->subjectUnderTest->getMentorsWithin(15);

        $this->assertCount(1, $rts);
    }

    /** @test */
    public function itFormatsTheReturnDataForAnRtsCorrectly()
    {
        $member = factory(Member::class)->create();
        $position = factory(Position::class)->create(['rts_id' => 15]);

        factory(PositionValidation::class)->create([
            'member_id' => $member->id,
            'status' => 5,
            'position_id' => $position->id,
        ]);

        $return = $this->subjectUnderTest->getMentorsWithin(15);

        $this->assertEquals($return, collect($member->cid));
    }

    /** @test */
    public function itCanReturnAListOfMentorsOfAnAirport()
    {
        $position = factory(Position::class)->create(['callsign' => 'EGKK_GND']);

        $positionValidation = factory(PositionValidation::class)->create([
            'status' => 5,
            'position_id' => $position->id,
        ]);

        $mentors = $this->subjectUnderTest->getMentorsFor('EGKK');

        $this->assertEquals($mentors->first(), $positionValidation->member->cid);
    }

    /** @test */
    public function itCanReturnAListOfMentorsOfASpecificCallsign()
    {
        $position = factory(Position::class)->create(['callsign' => 'EGKK_GND']);

        $positionValidation = factory(PositionValidation::class)->create([
            'status' => 5,
            'position_id' => $position->id,
        ]);

        $mentors = $this->subjectUnderTest->getMentorsFor('EGKK_GND');

        $this->assertEquals($mentors->first(), $positionValidation->member->cid);
    }

    /** @test */
    public function itDoesNotReturnMentorsWithoutPermissionToMentorAPosition()
    {
        $position = factory(Position::class)->create(['callsign' => 'EGKK_APP']);

        factory(PositionValidation::class)->create([
            'status' => 5,
            'position_id' => $position->id,
        ]);

        $mentors = $this->subjectUnderTest->getMentorsFor('EGCC_GND');

        $this->assertNull($mentors->first());
    }

    /** @test */
    public function itOnlyReturnsAMentorOnceOnAirportOrCallsignSearches()
    {
        $member = factory(Member::class)->create();

        $positionOne = factory(Position::class)->create(['callsign' => 'EGKK_APP']);
        $positionTwo = factory(Position::class)->create(['callsign' => 'EGKK_TWR']);

        factory(PositionValidation::class)->create([
            'member_id' => $member->id,
            'status' => 5,
            'position_id' => $positionOne->id,
        ]);

        factory(PositionValidation::class)->create([
            'member_id' => $member->id,
            'status' => 5,
            'position_id' => $positionTwo->id,
        ]);

        $airport = $this->subjectUnderTest->getMentorsFor('EGKK');
        $position = $this->subjectUnderTest->getMentorsFor('EGKK_APP');

        $this->assertCount(1, $airport);
        $this->assertCount(1, $position);
    }

    /** @test */
    public function itFormatsTheReturnDataForAirportOrPositionSearchesCorrectly()
    {
        $member = factory(Member::class)->create();
        $position = factory(Position::class)->create(['callsign' => 'EGKK_APP']);

        factory(PositionValidation::class)->create([
            'member_id' => $member->id,
            'status' => 5,
            'position_id' => $position->id,
        ]);

        $return = $this->subjectUnderTest->getMentorsFor('EGKK');

        $this->assertEquals($return, collect($member->cid));
    }

    /** @test */
    public function itCanReturnAListOfMentorsForAPositionType()
    {
        $appMentor = factory(Member::class)->create();
        factory(PositionValidation::class)->create([
            'member_id' => $appMentor->id,
            'status' => 5,
            'position_id' => factory(Position::class)->create(['callsign' => 'EGKK_APP']),
        ]);
        $twrMentor = factory(Member::class)->create();
        factory(PositionValidation::class)->create([
            'member_id' => $twrMentor,
            'status' => 5,
            'position_id' => factory(Position::class)->create(['callsign' => 'EGKK_TWR']),
        ]);

        $return = $this->subjectUnderTest->getMentorsForPositionType('APP');
        $this->assertEquals($return, collect($appMentor->cid));

        $return = $this->subjectUnderTest->getMentorsForPositionType('TWR');
        $this->assertEquals($return, collect($twrMentor->cid));
    }


    /** @test */
    public function itDoesNotDuplicateMentorsForPositionTypes()
    {
        $appMentor = factory(Member::class)->create();
        factory(PositionValidation::class)->create([
            'member_id' => $appMentor,
            'status' => 5,
            'position_id' => factory(Position::class)->create(['callsign' => 'EGKK_APP']),
        ]);
        factory(PositionValidation::class)->create([
            'member_id' => $appMentor,
            'status' => 5,
            'position_id' => factory(Position::class)->create(['callsign' => 'EGLL_APP']),
        ]);

        $return = $this->subjectUnderTest->getMentorsForPositionType('APP');
        $this->assertEquals($return, collect($appMentor->cid));
    }
}
