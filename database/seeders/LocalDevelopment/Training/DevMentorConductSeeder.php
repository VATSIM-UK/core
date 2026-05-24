<?php

declare(strict_types=1);

namespace Database\Seeders\LocalDevelopment\Training;

use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\MentorPermissionService;
use Database\Seeders\LocalDevelopment\Training\Concerns\CreatesLinkedAccount;
use Database\Seeders\LocalDevelopment\Training\Concerns\SeedsDevMentoringSessions;
use Database\Seeders\LocalDevelopment\Training\Concerns\SeedsDevProgSheet;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Seeds mentoring conduct fixtures for a real local login account (default CID 10000005).
 *
 * @see database/seeders/LocalDevelopment/README.md
 */
class DevMentorConductSeeder extends Seeder
{
    use CreatesLinkedAccount;
    use SeedsDevMentoringSessions;
    use SeedsDevProgSheet;

    public function run(): void
    {
        $this->ensurePrerequisites();

        $mentor = $this->resolveMentorAccount();
        $mentorMember = Member::query()->where('cid', $mentor->id)->firstOrFail();
        $studentMember = Member::query()->where('cid', DevTrainingPersonas::STUDENT_EXAMS_CID)->firstOrFail();

        $this->seedDevProgSheet();

        $mentor->givePermissionTo([
            'training.access',
            'training.beta',
            'training.mentors.view.atc',
        ]);

        $trainingPosition = DevTrainingFoundation::$trainingPositionsByCallsign['EGKK_TWR']
            ?? TrainingPosition::query()->where('cts_primary_position', 'EGKK_TWR')->firstOrFail();

        app(MentorPermissionService::class)->assignToMentorable(
            $mentor,
            $trainingPosition,
            $mentor,
            'S2 Training',
        );

        $session = $this->seedDevPendingConductSession($studentMember, $mentorMember, 'EGKK_TWR');

        $this->command?->info("Mentor conduct fixtures seeded for CID {$mentor->id}.");
        $this->command?->line("Conduct URL: /training/mentoring/conduct/{$session->id}");
        $this->command?->line('Training panel → Mentoring → Accepted Mentoring Sessions → Conduct');
    }

    private function resolveMentorAccount(): Account
    {
        $existing = Account::query()->find(DevTrainingPersonas::MENTOR_CONDUCT_CID);

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

        return $this->createLinkedAccount(
            DevTrainingPersonas::MENTOR_CONDUCT_CID,
            'Dev',
            'Mentor Conduct',
            'dev-mentor-conduct@example.test',
        );
    }

    private function ensurePrerequisites(): void
    {
        if (TrainingPosition::query()->where('cts_primary_position', 'EGKK_TWR')->doesntExist()) {
            throw new RuntimeException(
                'Run AtcAndCtsTrainingPositionsSeeder (or LocalDevelopmentTrainingSeeder) before DevMentorConductSeeder.',
            );
        }

        if (Member::query()->where('cid', DevTrainingPersonas::STUDENT_EXAMS_CID)->doesntExist()) {
            throw new RuntimeException(
                'Run CtsExamsAndMentoringSeeder (or LocalDevelopmentTrainingSeeder) before DevMentorConductSeeder.',
            );
        }
    }
}
