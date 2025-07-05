<?php

namespace Tests\Unit\CTS;

use App\Models\Cts\Member;
use App\Models\Cts\Position;
use App\Models\Cts\PositionValidation;
use App\Repositories\Cts\MentorRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_can_return_a_list_of_mentors_of_an_rts()
    {
        $position = Position::factory()->create(['rts_id' => 15]);

        $positionValidation = PositionValidation::Factory()->create([
            'status' => 5,
            'position_id' => $position->id,
        ]);

        $mentors = $this->subjectUnderTest->getMentorsWithin(15);

        $this->assertEquals($mentors->first(), $positionValidation->member->cid);
    }

    #[Test]
    public function it_does_not_return_mentors_of_another_rts()
    {
        $position = Position::factory()->create(['rts_id' => 15]);

        PositionValidation::Factory()->create([
            'status' => 5,
            'position_id' => $position->id,
        ]);

        $mentors = $this->subjectUnderTest->getMentorsWithin(10);

        $this->assertNull($mentors->first());
    }

    #[Test]
    public function it_only_returns_a_mentor_once_within_an_rts()
    {
        $member = Member::Factory()->create();

        $positionOne = Position::factory()->create(['rts_id' => 15]);
        $positionTwo = Position::factory()->create(['rts_id' => 15]);

        PositionValidation::Factory()->create([
            'member_id' => $member->id,
            'status' => 5,
            'position_id' => $positionOne->id,
        ]);

        PositionValidation::Factory()->create([
            'member_id' => $member->id,
            'status' => 5,
            'position_id' => $positionTwo->id,
        ]);

        $rts = $this->subjectUnderTest->getMentorsWithin(15);

        $this->assertCount(1, $rts);
    }

    #[Test]
    public function it_formats_the_return_data_for_an_rts_correctly()
    {
        $member = Member::Factory()->create();
        $position = Position::factory()->create(['rts_id' => 15]);

        PositionValidation::Factory()->create([
            'member_id' => $member->id,
            'status' => 5,
            'position_id' => $position->id,
        ]);

        $return = $this->subjectUnderTest->getMentorsWithin(15);

        $this->assertEquals($return, collect($member->cid));
    }

    #[Test]
    public function it_can_return_a_list_of_mentors_of_an_airport()
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_GND']);

        $positionValidation = PositionValidation::Factory()->create([
            'status' => 5,
            'position_id' => $position->id,
        ]);

        $mentors = $this->subjectUnderTest->getMentorsFor('EGKK');

        $this->assertEquals($mentors->first(), $positionValidation->member->cid);
    }

    #[Test]
    public function it_can_return_a_list_of_mentors_of_a_specific_callsign()
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_GND']);

        $positionValidation = PositionValidation::Factory()->create([
            'status' => 5,
            'position_id' => $position->id,
        ]);

        $mentors = $this->subjectUnderTest->getMentorsFor('EGKK_GND');

        $this->assertEquals($mentors->first(), $positionValidation->member->cid);
    }

    #[Test]
    public function it_does_not_return_mentors_without_permission_to_mentor_a_position()
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        PositionValidation::Factory()->create([
            'status' => 5,
            'position_id' => $position->id,
        ]);

        $mentors = $this->subjectUnderTest->getMentorsFor('EGCC_GND');

        $this->assertNull($mentors->first());
    }

    #[Test]
    public function it_only_returns_a_mentor_once_on_airport_or_callsign_searches()
    {
        $member = Member::Factory()->create();

        $positionOne = Position::factory()->create(['callsign' => 'EGKK_APP']);
        $positionTwo = Position::factory()->create(['callsign' => 'EGKK_TWR']);

        PositionValidation::Factory()->create([
            'member_id' => $member->id,
            'status' => 5,
            'position_id' => $positionOne->id,
        ]);

        PositionValidation::Factory()->create([
            'member_id' => $member->id,
            'status' => 5,
            'position_id' => $positionTwo->id,
        ]);

        $airport = $this->subjectUnderTest->getMentorsFor('EGKK');
        $position = $this->subjectUnderTest->getMentorsFor('EGKK_APP');

        $this->assertCount(1, $airport);
        $this->assertCount(1, $position);
    }

    #[Test]
    public function it_formats_the_return_data_for_airport_or_position_searches_correctly()
    {
        $member = Member::Factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        PositionValidation::Factory()->create([
            'member_id' => $member->id,
            'status' => 5,
            'position_id' => $position->id,
        ]);

        $return = $this->subjectUnderTest->getMentorsFor('EGKK');

        $this->assertEquals($return, collect($member->cid));
    }
}
