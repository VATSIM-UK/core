<?php

declare(strict_types=1);

namespace Database\Seeders\LocalDevelopment\Training;

use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\MentorPermissionService;
use Database\Seeders\LocalDevelopment\Training\Concerns\CreatesLinkedAccount;
use Database\Seeders\LocalDevelopment\Training\Concerns\SeedsDevMentoringSessions;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Seeds mentoring GANTT fixtures so a mentor can demo TECH-730 mentor unavailability on today's timeline.
 *
 * @see database/seeders/LocalDevelopment/README.md
 */
class DevMentorUnavailabilitySeeder extends Seeder
{
    use CreatesLinkedAccount;
    use SeedsDevMentoringSessions;

    public function run(): void
    {
        $this->ensurePrerequisites();

        $mentor = $this->resolveMentorAccount();
        $mentorMember = Member::query()->where('cid', $mentor->id)->firstOrFail();

        $busyStudent = Member::query()->where('cid', DevTrainingPersonas::STUDENT_EXAMS_CID)->firstOrFail();
        $overlappingStudent = Member::query()->where('cid', DevTrainingPersonas::STUDENT_CID)->firstOrFail();
        $clearStudent = Member::query()->where('cid', DevTrainingPersonas::STUDENT_LOA_CID)->firstOrFail();

        $mentor->givePermissionTo([
            'training.access',
            'training.beta',
            'training.mentors.view.atc',
        ]);

        $this->assignMentorToPosition($mentor, 'EGKK_TWR', 'S2 Training');
        $this->assignMentorToPosition($mentor, 'EGLL_N_APP', 'S3 Training');

        $today = now()->format('Y-m-d');

        $this->seedDevAcceptedSessionForDate(
            $busyStudent,
            $mentorMember,
            'EGKK_TWR',
            $today,
            '18:00:00',
            '20:00:00',
        );

        $this->seedDevMentoringPendingRequest($overlappingStudent, 'EGLL_N_APP');
        Availability::query()->updateOrCreate(
            [
                'student_id' => $overlappingStudent->id,
                'date' => $today,
            ],
            [
                'from' => '17:00:00',
                'to' => '21:00:00',
                'type' => 'S',
            ],
        );

        $this->seedDevMentoringPendingRequest($clearStudent, 'EGKK_TWR');
        Availability::query()->updateOrCreate(
            [
                'student_id' => $clearStudent->id,
                'date' => $today,
            ],
            [
                'from' => '14:00:00',
                'to' => '16:00:00',
                'type' => 'S',
            ],
        );

        $this->command?->info("Mentor unavailability fixtures seeded for CID {$mentor->id}.");
        $this->command?->line('Training panel → Mentoring → today: My sessions 18:00–20:00, overlapping pickup on EGLL_N_APP, clear pickup 14:00–16:00.');
    }

    private function resolveMentorAccount(): Account
    {
        $cid = (int) (env('DEV_MENTOR_CID') ?: DevTrainingPersonas::MENTOR_CONDUCT_CID);
        $existing = Account::query()->find($cid);

        if ($existing !== null) {
            Member::query()->firstOrCreate(
                ['cid' => $existing->id],
                [
                    'id' => $existing->id,
                    'name' => $existing->name,
                    'joined' => now(),
                    'joined_div' => now(),
                ],
            );

            return $existing->fresh();
        }

        if ($cid !== DevTrainingPersonas::MENTOR_CONDUCT_CID) {
            throw new RuntimeException(
                "Account {$cid} (DEV_MENTOR_CID) was not found. Create/login that sandbox account first, or omit DEV_MENTOR_CID.",
            );
        }

        return $this->createLinkedAccount(
            DevTrainingPersonas::MENTOR_CONDUCT_CID,
            'Dev',
            'Mentor Conduct',
            'dev-mentor-conduct@example.test',
        );
    }

    private function assignMentorToPosition(Account $mentor, string $callsign, string $category): void
    {
        $trainingPosition = DevTrainingFoundation::$trainingPositionsByCallsign[$callsign]
            ?? TrainingPosition::query()->where('cts_primary_position', $callsign)->firstOrFail();

        app(MentorPermissionService::class)->assignToMentorable(
            $mentor,
            $trainingPosition,
            $mentor,
            $category,
        );
    }

    private function ensurePrerequisites(): void
    {
        if (TrainingPosition::query()->where('cts_primary_position', 'EGKK_TWR')->doesntExist()) {
            throw new RuntimeException(
                'Run AtcAndCtsTrainingPositionsSeeder (or LocalDevelopmentTrainingSeeder) before DevMentorUnavailabilitySeeder.',
            );
        }

        if (TrainingPosition::query()->where('cts_primary_position', 'EGLL_N_APP')->doesntExist()) {
            throw new RuntimeException(
                'Run AtcAndCtsTrainingPositionsSeeder (or LocalDevelopmentTrainingSeeder) before DevMentorUnavailabilitySeeder.',
            );
        }

        foreach ([
            DevTrainingPersonas::STUDENT_EXAMS_CID,
            DevTrainingPersonas::STUDENT_CID,
            DevTrainingPersonas::STUDENT_LOA_CID,
        ] as $cid) {
            if (Member::query()->where('cid', $cid)->doesntExist()) {
                throw new RuntimeException(
                    'Run LocalDevelopmentTrainingSeeder (personas + CTS mentoring seeders) before DevMentorUnavailabilitySeeder.',
                );
            }
        }
    }
}
