<?php

declare(strict_types=1);

namespace Database\Seeders\LocalDevelopment\Training;

use App\Models\Atc\Position;
use App\Models\Cts\Availability;
use App\Models\Cts\CancelReason;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExaminerSettings;
use App\Models\Cts\ExamSetup;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalExaminers;
use App\Models\Cts\PracticalResult;
use App\Services\Training\MentorPermissionService;
use Database\Seeders\LocalDevelopment\Training\Concerns\AssignsDevTrainingRoles;
use Database\Seeders\LocalDevelopment\Training\Concerns\CreatesDevTrainingPlace;
use Database\Seeders\LocalDevelopment\Training\Concerns\CreatesLinkedAccount;
use Database\Seeders\LocalDevelopment\Training\Concerns\SeedsCtsPosition;
use Database\Seeders\LocalDevelopment\Training\Concerns\SeedsDevMentoringSessions;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Seeds CTS practical exams (request, scheduled, completed, cancelled), mentoring sessions, and mentor assignments.
 *
 * @see database/seeders/LocalDevelopment/README.md
 */
class CtsExamsAndMentoringSeeder extends Seeder
{
    use AssignsDevTrainingRoles;
    use CreatesDevTrainingPlace;
    use CreatesLinkedAccount;
    use SeedsCtsPosition;
    use SeedsDevMentoringSessions;

    public function run(): void
    {
        $this->ensurePrerequisites();

        $staffMember = Member::query()->where('cid', DevTrainingFoundation::$staff->id)->firstOrFail();

        Member::query()->where('cid', DevTrainingFoundation::$staff->id)->update(['examiner' => true]);

        ExaminerSettings::query()->updateOrCreate(
            ['memberID' => $staffMember->id],
            [
                'OBS' => 0,
                'S1' => 1,
                'S2' => 0,
                'S3' => 0,
                'P1' => 0,
                'P2' => 0,
                'P3' => 0,
                'P4' => 0,
                'P5' => 0,
                'lastUpdated' => now(),
                'updatedBy' => DevTrainingFoundation::$staff->id,
            ],
        );

        DevTrainingFoundation::$studentExams = $this->createLinkedAccount(
            DevTrainingPersonas::STUDENT_EXAMS_CID,
            'Dev',
            'Training Student Exams',
            DevTrainingPersonas::STUDENT_EXAMS_EMAIL,
        );
        $this->assignDevTrainingStudentRole(DevTrainingFoundation::$studentExams);

        $examStudentMember = Member::query()->where('cid', DevTrainingFoundation::$studentExams->id)->firstOrFail();

        $examPlace = $this->createDevTrainingPlace(
            DevTrainingFoundation::$studentExams,
            'EGKK_TWR',
            'Dev seed: student with practical exam scenarios.',
        );
        DevTrainingFoundation::$trainingPlacesByKey['exams'] = $examPlace;

        Availability::query()->updateOrCreate(
            [
                'student_id' => $examStudentMember->id,
                'date' => now()->addDays(5)->format('Y-m-d'),
            ],
            [
                'from' => '14:00:00',
                'to' => '18:00:00',
                'type' => 'S',
            ],
        );

        $this->seedExamRequest($examStudentMember, $staffMember);
        $this->seedScheduledExam($examStudentMember, $staffMember);
        $this->seedCompletedExam($examStudentMember, $staffMember);
        $this->seedCancelledExam($examStudentMember);
        $this->seedDevMentoringHistory($examStudentMember, $staffMember, 'EGKK_TWR');
        $this->seedDevMentoringPendingRequest($examStudentMember, 'EGKK_TWR');
        $this->seedMentorAssignment();

        $this->command?->info('CTS exams, sessions, and mentor assignment seeded.');
    }

    private function ensurePrerequisites(): void
    {
        if (DevTrainingFoundation::$staff === null) {
            throw new RuntimeException('Run DevTrainingPersonasSeeder before CtsExamsAndMentoringSeeder.');
        }

        if (! isset(DevTrainingFoundation::$trainingPositionsByCallsign['EGKK_TWR'])) {
            throw new RuntimeException('Run AtcAndCtsTrainingPositionsSeeder before CtsExamsAndMentoringSeeder.');
        }
    }

    private function seedExamRequest(Member $student, Member $setupBy): void
    {
        $booking = ExamBooking::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'position_1' => 'EGKK_TWR',
                'taken' => 0,
                'finished' => ExamBooking::NOT_FINISHED_FLAG,
            ],
            [
                'exam' => 'TWR',
                'student_rating' => 1,
            ],
        );

        ExamSetup::query()->updateOrCreate(
            ['bookid' => $booking->id],
            [
                'rts_id' => 1,
                'student_id' => $student->id,
                'position_1' => 'EGKK_TWR',
                'position_2' => null,
                'exam' => 'TWR',
                'setup_by' => $setupBy->id,
                'setup_date' => now()->toDateTimeString(),
                'booked' => 0,
            ],
        );
    }

    private function seedScheduledExam(Member $student, Member $examiner): void
    {
        $booking = ExamBooking::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'position_1' => 'EGLL_N_APP',
                'taken' => 1,
                'finished' => ExamBooking::NOT_FINISHED_FLAG,
            ],
            [
                'exam' => 'APP',
                'student_rating' => 1,
                'taken_date' => now()->addDays(7)->format('Y-m-d'),
                'taken_from' => '14:00:00',
                'taken_to' => '16:00:00',
                'exmr_id' => $examiner->id,
                'exmr_rating' => 3,
                'time_book' => now(),
            ],
        );

        PracticalExaminers::query()->updateOrCreate(
            ['examid' => $booking->id],
            ['senior' => $examiner->id],
        );

        ExamSetup::query()->updateOrCreate(
            ['bookid' => $booking->id],
            [
                'rts_id' => 1,
                'student_id' => $student->id,
                'position_1' => 'EGLL_N_APP',
                'position_2' => null,
                'exam' => 'APP',
                'setup_by' => $examiner->id,
                'setup_date' => now()->toDateTimeString(),
                'booked' => 1,
            ],
        );
    }

    private function seedCompletedExam(Member $student, Member $examiner): void
    {
        $booking = ExamBooking::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'position_1' => 'EGKK_TWR',
                'finished' => ExamBooking::FINISHED_FLAG,
            ],
            [
                'exam' => 'TWR',
                'student_rating' => 1,
                'taken' => 1,
                'taken_date' => now()->subMonth()->format('Y-m-d'),
                'taken_from' => '10:00:00',
                'taken_to' => '12:00:00',
                'exmr_id' => $examiner->id,
                'exmr_rating' => 3,
                'time_book' => now()->subMonth(),
            ],
        );

        PracticalExaminers::query()->updateOrCreate(
            ['examid' => $booking->id],
            ['senior' => $examiner->id],
        );

        PracticalResult::query()->updateOrCreate(
            [
                'examid' => $booking->id,
                'student_id' => $student->id,
            ],
            [
                'exam' => 'TWR',
                'result' => 'P',
                'date' => now()->subMonth(),
            ],
        );
    }

    private function seedCancelledExam(Member $student): void
    {
        $this->seedCtsPosition('EGLL_N_TWR', Position::TYPE_TOWER);

        $booking = ExamBooking::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'position_1' => 'EGLL_N_TWR',
                'taken' => 0,
                'finished' => ExamBooking::NOT_FINISHED_FLAG,
            ],
            [
                'exam' => 'TWR',
                'student_rating' => 1,
                'taken_date' => null,
                'taken_from' => null,
                'taken_to' => null,
            ],
        );

        CancelReason::query()->updateOrCreate(
            [
                'sesh_id' => $booking->id,
                'sesh_type' => 'EX',
            ],
            [
                'reason' => 'Dev seed: student cancelled a scheduled exam.',
                'used' => 0,
                'reason_by' => $student->cid,
                'date' => now()->subDays(2),
            ],
        );
    }

    private function seedMentorAssignment(): void
    {
        $trainingPosition = DevTrainingFoundation::$trainingPositionsByCallsign['EGKK_TWR'];
        $staff = DevTrainingFoundation::$staff;

        app(MentorPermissionService::class)->assignToMentorable(
            $staff,
            $trainingPosition,
            $staff,
            'S2 Training',
        );
    }
}
