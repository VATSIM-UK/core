<?php

namespace Tests\Unit\Mship;

use App\Models\Mship\Qualification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QualificationDetectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function itHandlesMinus1ReportedAsP0()
    {
        $qualifications = Qualification::parseVatsimPilotQualifications(-1);

        $p0_qualification = Qualification::where('code', 'P0')->first();

        $this->assertTrue(collect($qualifications)->pluck('code')->contains($p0_qualification->code));
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
            [1, 'P1'],
            [2, 'P2'],
            [4, 'P3'],
            [8, 'P4'],
            [16, 'P5'],
        ];
    }
}
