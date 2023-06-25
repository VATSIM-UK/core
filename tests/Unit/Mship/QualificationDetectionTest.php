<?php

namespace Tests\Unit\Mship;

use App\Models\Mship\Qualification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QualificationDetectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function itHandlesMinus1ByNotAssigningPilotRatings()
    {
        $qualifications = Qualification::parseVatsimPilotQualifications(-1);

        $this->assertEmpty($qualifications);
    }

    /** @test */
    public function itHandlesFlightInstructorRating()
    {
        $flightInstructorQualification = Qualification::where('code', 'FI')->first();

        $qualifications = Qualification::parseVatsimPilotQualifications($flightInstructorQualification->vatsim);

        $this->assertTrue(collect($qualifications)->pluck('code')->contains($flightInstructorQualification->code));
    }

    /** @test */
    public function itHandlesFlightExaminerRating()
    {
        $flightExaminerQualification = Qualification::where('code', 'FE')->first();

        $qualifications = Qualification::parseVatsimPilotQualifications($flightExaminerQualification->vatsim);

        $this->assertTrue(collect($qualifications)->pluck('code')->contains($flightExaminerQualification->code));
    }

    /** @test @dataProvider pilotRatingsTestData */
    public function itHandlesNormalPilotRatings($networkBitmask, $expectedCode)
    {
        $qualifications = Qualification::parseVatsimPilotQualifications($networkBitmask);

        $this->assertTrue(collect($qualifications)->pluck('code')->contains($expectedCode));
    }

    protected function pilotRatingsTestData()
    {
        return [
            // [Bitmask, Expected Code]
            [1, 'PPL'],
            [3, 'IR'],
            [7, 'CMEL'],
            [15, 'ATPL'],
        ];
    }
}
