<?php

namespace Tests\Unit\Mship;

use App\Models\Mship\Qualification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Metadata\DataProvider;
use Tests\TestCase;

class QualificationDetectionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_handles_minus1_by_not_assigning_pilot_ratings()
    {
        $qualifications = Qualification::parseVatsimPilotQualifications(-1);

        $this->assertEmpty($qualifications);
    }

    #[Test]
    public function it_handles_flight_instructor_rating()
    {
        $flightInstructorQualification = Qualification::where('code', 'FI')->first();

        $qualifications = Qualification::parseVatsimPilotQualifications($flightInstructorQualification->vatsim);

        $this->assertTrue(collect($qualifications)->pluck('code')->contains($flightInstructorQualification->code));
    }

    #[Test]
    public function it_handles_flight_examiner_rating()
    {
        $flightExaminerQualification = Qualification::where('code', 'FE')->first();

        $qualifications = Qualification::parseVatsimPilotQualifications($flightExaminerQualification->vatsim);

        $this->assertTrue(collect($qualifications)->pluck('code')->contains($flightExaminerQualification->code));
    }

    #[DataProvider('pilotRatingsTestData')]
    public function itHandlesNormalPilotRatings($networkBitmask, $expectedCode)
    {
        $qualifications = Qualification::parseVatsimPilotQualifications($networkBitmask);

        $this->assertTrue(collect($qualifications)->pluck('code')->contains($expectedCode));
    }

    public static function pilotRatingsTestData()
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
