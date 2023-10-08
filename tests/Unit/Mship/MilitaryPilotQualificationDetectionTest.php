<?php

namespace Tests\Unit\Mship;

use App\Models\Mship\Qualification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MilitaryPilotQualificationDetectionTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('militaryRatingsTestData')]
    public function itHandlesMilitaryPilotRatings($networkBitmask, $expectedCode)
    {
        $qualifications = Qualification::parseVatsimMilitaryPilotQualifications($networkBitmask);

        $this->assertTrue(collect($qualifications)->pluck('code')->contains($expectedCode));
    }

    public static function militaryRatingsTestData()
    {
        return [
            // [Bitmask, Expected Code]
            [0, 'M0'],
            [1, 'M1'],
            [3, 'M2'],
            [7, 'M3'],
            [15, 'M4'],
        ];
    }
}
