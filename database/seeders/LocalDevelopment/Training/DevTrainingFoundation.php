<?php

declare(strict_types=1);

namespace Database\Seeders\LocalDevelopment\Training;

use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;

/**
 * Shared state populated across local development training seeders in a single run.
 *
 * @see database/seeders/LocalDevelopment/README.md
 */
final class DevTrainingFoundation
{
    /** @var array<string, TrainingPosition> */
    public static array $trainingPositionsByCallsign = [];

    /** @var array<string, TrainingPlace> */
    public static array $trainingPlacesByKey = [];

    public static ?Account $staff = null;

    public static ?Account $student = null;

    public static ?Account $studentLoa = null;

    public static ?Account $studentExams = null;

    public static function reset(): void
    {
        self::$trainingPositionsByCallsign = [];
        self::$trainingPlacesByKey = [];
        self::$staff = null;
        self::$student = null;
        self::$studentLoa = null;
        self::$studentExams = null;
    }
}
