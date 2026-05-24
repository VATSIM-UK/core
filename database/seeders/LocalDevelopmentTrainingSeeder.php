<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\LocalDevelopment\Training\AtcAndCtsTrainingPositionsSeeder;
use Database\Seeders\LocalDevelopment\Training\CtsExamsAndMentoringSeeder;
use Database\Seeders\LocalDevelopment\Training\DevTrainingFoundation;
use Database\Seeders\LocalDevelopment\Training\DevTrainingPersonas;
use Database\Seeders\LocalDevelopment\Training\DevTrainingPersonasSeeder;
use Database\Seeders\LocalDevelopment\Training\DevTrainingRolesSeeder;
use Database\Seeders\LocalDevelopment\Training\TrainingPlaceAvailabilitySeeder;
use Illuminate\Database\Seeder;

/**
 * Opt-in orchestrator for local training panel development data.
 *
 * @see database/seeders/LocalDevelopment/README.md
 */
class LocalDevelopmentTrainingSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('LocalDevelopmentTrainingSeeder may only be run in the local or testing environment.');

            return;
        }

        DevTrainingFoundation::reset();

        $this->call([
            AtcAndCtsTrainingPositionsSeeder::class,
            DevTrainingRolesSeeder::class,
            DevTrainingPersonasSeeder::class,
            TrainingPlaceAvailabilitySeeder::class,
            CtsExamsAndMentoringSeeder::class,
        ]);

        $this->printSummary();
    }

    private function printSummary(): void
    {
        if ($this->command === null) {
            return;
        }

        $trainingPositions = implode(', ', array_keys(DevTrainingFoundation::$trainingPositionsByCallsign));

        $this->command->newLine();
        $this->command->info('Local development training data seeded.');
        $this->command->table(
            ['Persona', 'CID', 'Email', 'Notes'],
            [
                [
                    'Staff',
                    (string) DevTrainingPersonas::STAFF_CID,
                    DevTrainingPersonas::STAFF_EMAIL,
                    'Admin, examiner (TWR/APP), mentor',
                ],
                [
                    'Student',
                    (string) DevTrainingPersonas::STUDENT_CID,
                    DevTrainingPersonas::STUDENT_EMAIL,
                    'Training place with availability checks, warning, mentoring history',
                ],
                [
                    'Student (LOA)',
                    (string) DevTrainingPersonas::STUDENT_LOA_CID,
                    DevTrainingPersonas::STUDENT_LOA_EMAIL,
                    'LOA on EGLL_N_APP; mentoring history on training place',
                ],
                [
                    'Student (exams)',
                    (string) DevTrainingPersonas::STUDENT_EXAMS_CID,
                    DevTrainingPersonas::STUDENT_EXAMS_EMAIL,
                    'Exams + mentoring history; open mentoring request',
                ],
            ],
        );
        $this->command->line("Training positions: {$trainingPositions}");
        $this->command->line('Training panel: /training');
        $this->command->newLine();
        $this->command->comment('Log in with your sandbox CID + grant:superman, then impersonate the personas above from admin.');
    }
}
