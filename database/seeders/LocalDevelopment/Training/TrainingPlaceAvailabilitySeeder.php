<?php

declare(strict_types=1);

namespace Database\Seeders\LocalDevelopment\Training;

use App\Enums\AvailabilityCheckStatus;
use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Models\Training\TrainingPlace\AvailabilityCheck;
use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlaceLeaveOfAbsence;
use Carbon\Carbon;
use Database\Seeders\LocalDevelopment\Training\Concerns\AssignsDevTrainingRoles;
use Database\Seeders\LocalDevelopment\Training\Concerns\CreatesDevTrainingPlace;
use Database\Seeders\LocalDevelopment\Training\Concerns\CreatesLinkedAccount;
use Database\Seeders\LocalDevelopment\Training\Concerns\SeedsDevMentoringSessions;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Seeds training places with availability checks, warnings, leave of absence, and CTS availability.
 *
 * @see database/seeders/LocalDevelopment/README.md
 */
class TrainingPlaceAvailabilitySeeder extends Seeder
{
    use AssignsDevTrainingRoles;
    use CreatesDevTrainingPlace;
    use CreatesLinkedAccount;
    use SeedsDevMentoringSessions;

    public function run(): void
    {
        $this->ensurePrerequisites();

        DevTrainingFoundation::$studentLoa = $this->createLinkedAccount(
            DevTrainingPersonas::STUDENT_LOA_CID,
            'Dev',
            'Training Student LOA',
            DevTrainingPersonas::STUDENT_LOA_EMAIL,
        );
        $this->assignDevTrainingStudentRole(DevTrainingFoundation::$studentLoa);

        $availabilityPlace = $this->createDevTrainingPlace(
            DevTrainingFoundation::$student,
            'EGKK_TWR',
            'Dev seed: student with availability checks and warnings.',
        );
        DevTrainingFoundation::$trainingPlacesByKey['availability'] = $availabilityPlace;

        $this->seedAvailabilityChecksAndWarning($availabilityPlace);

        $staffMember = Member::query()->where('cid', DevTrainingFoundation::$staff->id)->firstOrFail();
        $studentMember = Member::query()->where('cid', DevTrainingFoundation::$student->id)->firstOrFail();
        $this->seedDevMentoringHistory($studentMember, $staffMember, 'EGKK_TWR');

        Availability::query()->updateOrCreate(
            [
                'student_id' => $studentMember->id,
                'date' => now()->addDays(3)->format('Y-m-d'),
            ],
            [
                'from' => '18:00:00',
                'to' => '22:00:00',
                'type' => 'S',
            ],
        );

        $loaPlace = $this->createDevTrainingPlace(
            DevTrainingFoundation::$studentLoa,
            'EGLL_N_APP',
            'Dev seed: student on leave of absence.',
        );
        DevTrainingFoundation::$trainingPlacesByKey['loa'] = $loaPlace;

        TrainingPlaceLeaveOfAbsence::query()->updateOrCreate(
            [
                'training_place_id' => $loaPlace->id,
                'reason' => 'Dev seed: short training break.',
            ],
            [
                'begins_at' => Carbon::today()->subDay(),
                'ends_at' => Carbon::today()->addDays(9)->endOfDay(),
            ],
        );

        $loaStudentMember = Member::query()->where('cid', DevTrainingFoundation::$studentLoa->id)->firstOrFail();
        $this->seedDevMentoringHistory($loaStudentMember, $staffMember, 'EGLL_N_APP');

        $this->command?->info('Training place availability, warnings, LOA, mentoring history, and CTS availability seeded.');
    }

    private function ensurePrerequisites(): void
    {
        if (DevTrainingFoundation::$student === null || DevTrainingFoundation::$staff === null) {
            throw new RuntimeException('Run DevTrainingPersonasSeeder before TrainingPlaceAvailabilitySeeder.');
        }

        if (DevTrainingFoundation::$trainingPositionsByCallsign === []) {
            throw new RuntimeException('Run AtcAndCtsTrainingPositionsSeeder before TrainingPlaceAvailabilitySeeder.');
        }
    }

    private function seedAvailabilityChecksAndWarning(\App\Models\Training\TrainingPlace\TrainingPlace $trainingPlace): void
    {
        $passedCheck = AvailabilityCheck::query()->updateOrCreate(
            [
                'training_place_id' => $trainingPlace->id,
                'status' => AvailabilityCheckStatus::Passed,
            ],
            [
                'created_at' => now()->subWeek(),
                'updated_at' => now()->subWeek(),
            ],
        );

        $failedCheck = AvailabilityCheck::query()->updateOrCreate(
            [
                'training_place_id' => $trainingPlace->id,
                'status' => AvailabilityCheckStatus::Failed,
            ],
            [
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
        );

        AvailabilityWarning::query()->updateOrCreate(
            [
                'training_place_id' => $trainingPlace->id,
                'availability_check_id' => $failedCheck->id,
                'status' => 'pending',
            ],
            [
                'expires_at' => now()->addDays(5),
            ],
        );
    }
}
