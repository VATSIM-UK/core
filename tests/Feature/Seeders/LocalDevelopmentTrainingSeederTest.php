<?php

declare(strict_types=1);

namespace Tests\Feature\Seeders;

use App\Enums\AvailabilityCheckStatus;
use App\Models\Cts\CancelReason;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamSetup;
use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPlace\TrainingPlaceLeaveOfAbsence;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Database\Seeders\LocalDevelopment\Training\DevTrainingPersonas;
use Database\Seeders\LocalDevelopmentTrainingSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LocalDevelopmentTrainingSeederTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_seeds_local_development_training_data(): void
    {
        $this->seed(LocalDevelopmentTrainingSeeder::class);

        $this->assertDatabaseHas('training_positions', [
            'cts_primary_position' => 'EGKK_TWR',
        ]);

        $this->assertDatabaseHas('positions', [
            'callsign' => 'EGKK_TWR',
        ], 'cts');

        $this->assertDatabaseHas('mship_account', [
            'id' => DevTrainingPersonas::STAFF_CID,
            'email' => DevTrainingPersonas::STAFF_EMAIL,
        ]);

        $this->assertDatabaseHas('mship_account', [
            'id' => DevTrainingPersonas::STUDENT_CID,
            'email' => DevTrainingPersonas::STUDENT_EMAIL,
        ]);

        $staff = Account::findOrFail(DevTrainingPersonas::STAFF_CID);
        $this->assertTrue($staff->hasRole(DevTrainingPersonas::STAFF_ROLE));
        $this->assertTrue($staff->hasPermissionTo('training.exams.setup'));
        $this->assertTrue($staff->hasPermissionTo('waiting-lists.view.atc'));

        $student = Account::findOrFail(DevTrainingPersonas::STUDENT_CID);
        $this->assertTrue($student->hasRole(DevTrainingPersonas::STUDENT_ROLE));
        $this->assertTrue($student->hasPermissionTo('training.access'));
        $this->assertFalse($student->hasPermissionTo('training.exams.setup'));

        $this->assertNotNull(Member::query()->where('cid', DevTrainingPersonas::STAFF_CID)->first());
        $this->assertNotNull(Member::query()->where('cid', DevTrainingPersonas::STUDENT_CID)->first());

        $this->assertGreaterThanOrEqual(2, TrainingPosition::query()->count());
        $this->assertGreaterThanOrEqual(2, CtsPosition::query()->whereIn('callsign', ['EGKK_TWR', 'EGLL_N_APP'])->count());

        $availabilityPlace = TrainingPlace::query()
            ->where('account_id', DevTrainingPersonas::STUDENT_CID)
            ->whereHas('trainingPosition', fn ($q) => $q->where('cts_primary_position', 'EGKK_TWR'))
            ->first();
        $this->assertNotNull($availabilityPlace);
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $availabilityPlace->id,
            'status' => AvailabilityCheckStatus::Failed->value,
        ]);
        $this->assertTrue(
            AvailabilityWarning::query()
                ->where('training_place_id', $availabilityPlace->id)
                ->where('status', 'pending')
                ->exists()
        );

        $this->assertTrue(
            TrainingPlaceLeaveOfAbsence::query()
                ->whereHas('trainingPlace', fn ($q) => $q->where('account_id', DevTrainingPersonas::STUDENT_LOA_CID))
                ->exists()
        );

        $examMemberId = Member::query()->where('cid', DevTrainingPersonas::STUDENT_EXAMS_CID)->value('id');
        $this->assertNotNull($examMemberId);
        $this->assertTrue(ExamBooking::query()->where('student_id', $examMemberId)->where('taken', 0)->where('finished', 0)->exists());
        $this->assertTrue(ExamBooking::query()->where('student_id', $examMemberId)->where('taken', 1)->where('finished', 0)->exists());
        $this->assertTrue(ExamBooking::query()->where('student_id', $examMemberId)->where('finished', 1)->exists());
        $this->assertTrue(CancelReason::query()->where('sesh_type', 'EX')->exists());
        $this->assertDatabaseHas('positions', ['callsign' => 'EGLL_N_TWR'], 'cts');
        $this->assertTrue(ExamSetup::query()->where('student_id', $examMemberId)->exists());
        $studentMemberId = Member::query()->where('cid', DevTrainingPersonas::STUDENT_CID)->value('id');
        $loaMemberId = Member::query()->where('cid', DevTrainingPersonas::STUDENT_LOA_CID)->value('id');
        $this->assertNotNull($studentMemberId);
        $this->assertNotNull($loaMemberId);

        foreach ([$studentMemberId, $loaMemberId, $examMemberId] as $memberId) {
            $this->assertTrue(
                Session::query()
                    ->where('student_id', $memberId)
                    ->where('session_done', 1)
                    ->where('taken', 1)
                    ->exists(),
                "Expected completed mentoring sessions for CTS member {$memberId}",
            );
            $this->assertTrue(
                Session::query()
                    ->where('student_id', $memberId)
                    ->whereNotNull('cancelled_datetime')
                    ->exists(),
                "Expected cancelled mentoring sessions for CTS member {$memberId}",
            );
        }

        $this->assertTrue(
            Session::query()
                ->where('student_id', $examMemberId)
                ->whereNull('mentor_id')
                ->where('session_done', 0)
                ->exists()
        );
        $this->assertTrue(
            MentorTrainingPosition::query()->where('account_id', DevTrainingPersonas::STAFF_CID)->exists()
        );
    }

    #[Test]
    public function it_is_idempotent(): void
    {
        $this->seed(LocalDevelopmentTrainingSeeder::class);
        $positionCount = TrainingPosition::query()->count();
        $staffEmail = Account::findOrFail(DevTrainingPersonas::STAFF_CID)->email;

        $this->seed(LocalDevelopmentTrainingSeeder::class);

        $this->assertSame($positionCount, TrainingPosition::query()->count());
        $this->assertSame($staffEmail, Account::findOrFail(DevTrainingPersonas::STAFF_CID)->email);
    }
}
