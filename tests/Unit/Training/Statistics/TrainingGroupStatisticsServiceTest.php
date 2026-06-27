<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Statistics;

use App\Models\Cts\Member;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Cts\PracticalResult;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\TrainingGroupStatisticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingGroupStatisticsServiceTest extends TestCase
{
    use DatabaseTransactions;

    private TrainingGroupStatisticsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TrainingGroupStatisticsService::class);
    }

    #[Test]
    public function it_counts_active_training_places_for_a_category(): void
    {
        $category = 'S2 Training';
        $trainingPosition = $this->createTrainingPosition($category, 'EGKK_TWR');

        $beforeCount = $this->service->activeTrainingPlacesCount($category);

        TrainingPlace::factory()->create([
            'account_id' => Account::factory()->create()->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        TrainingPlace::factory()->create([
            'account_id' => Account::factory()->create()->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        $otherPosition = $this->createTrainingPosition('S3 Training', 'EGLL_N_APP');
        TrainingPlace::factory()->create([
            'account_id' => Account::factory()->create()->id,
            'training_position_id' => $otherPosition->id,
        ]);

        $this->assertSame($beforeCount + 2, $this->service->activeTrainingPlacesCount($category));
    }

    #[Test]
    public function it_calculates_average_sessions_to_rating_for_completed_training_places(): void
    {
        $category = 'S2 Training';
        $callsign = 'EGKK_TWR';
        $trainingPosition = $this->createTrainingPosition($category, $callsign);

        $studentOne = $this->createAccountWithMember();
        $studentTwo = $this->createAccountWithMember();

        $this->createCompletedTrainingPlace($studentOne, $trainingPosition, now()->subDays(60), now()->subDays(10));
        $this->createCompletedTrainingPlace($studentTwo, $trainingPosition, now()->subDays(90), now()->subDays(20));

        $this->createCompletedSession($studentOne->member->id, $callsign, now()->subDays(30));
        $this->createCompletedSession($studentOne->member->id, $callsign, now()->subDays(20));
        $this->createCompletedSession($studentOne->member->id, $callsign, now()->subDays(15));

        $this->createCompletedSession($studentTwo->member->id, $callsign, now()->subDays(40));

        $this->assertSame(2.0, $this->service->averageSessionsToRating($category));
    }

    #[Test]
    public function it_calculates_average_training_duration_for_completed_training_places(): void
    {
        $category = 'S2 Training';
        $trainingPosition = $this->createTrainingPosition($category, 'EGKK_TWR');

        $studentOne = $this->createAccountWithMember();
        $studentTwo = $this->createAccountWithMember();

        $this->createCompletedTrainingPlace(
            $studentOne,
            $trainingPosition,
            Carbon::parse('2024-01-01'),
            Carbon::parse('2024-01-11'),
        );

        $this->createCompletedTrainingPlace(
            $studentTwo,
            $trainingPosition,
            Carbon::parse('2024-01-01'),
            Carbon::parse('2024-01-21'),
        );

        $this->assertSame(15.0, $this->service->averageTrainingDurationDays($category));
    }

    #[Test]
    public function it_calculates_exam_first_pass_rate_for_completed_students(): void
    {
        $category = 'S2 Training';
        $trainingPosition = $this->createTrainingPosition($category, 'EGKK_TWR');

        $passedStudent = $this->createAccountWithMember();
        $failedStudent = $this->createAccountWithMember();

        $this->createCompletedTrainingPlace($passedStudent, $trainingPosition, now()->subDays(90), now()->subDays(10));
        $this->createCompletedTrainingPlace($failedStudent, $trainingPosition, now()->subDays(90), now()->subDays(10));

        PracticalResult::factory()->create([
            'student_id' => $passedStudent->member->id,
            'exam' => 'TWR',
            'result' => PracticalResult::PASSED,
            'date' => now()->subDays(15),
        ]);

        PracticalResult::factory()->create([
            'student_id' => $failedStudent->member->id,
            'exam' => 'TWR',
            'result' => PracticalResult::FAILED,
            'date' => now()->subDays(15),
        ]);

        PracticalResult::factory()->create([
            'student_id' => $failedStudent->member->id,
            'exam' => 'TWR',
            'result' => PracticalResult::PASSED,
            'date' => now()->subDays(5),
        ]);

        $this->assertSame(50, $this->service->examFirstPassRate($category));
    }

    #[Test]
    public function it_returns_null_for_exam_first_pass_rate_when_category_has_no_exam_mapping(): void
    {
        $category = 'Heathrow GMC';
        $trainingPosition = $this->createTrainingPosition($category, 'EGLL_GMC');

        $student = $this->createAccountWithMember();
        $this->createCompletedTrainingPlace($student, $trainingPosition, now()->subDays(90), now()->subDays(10));

        $this->assertNull($this->service->examFirstPassRate($category));
    }

    #[Test]
    public function it_returns_null_averages_when_no_completed_training_places_exist(): void
    {
        $category = 'S2 Training';
        $trainingPosition = $this->createTrainingPosition($category, 'EGKK_TWR');

        TrainingPlace::factory()->create([
            'account_id' => Account::factory()->create()->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        $this->assertNull($this->service->averageSessionsToRating($category));
        $this->assertNull($this->service->averageTrainingDurationDays($category));
        $this->assertNull($this->service->examFirstPassRate($category));
    }

    private function createTrainingPosition(string $category, string $callsign): TrainingPosition
    {
        CtsPosition::firstOrCreate(['callsign' => $callsign]);

        return TrainingPosition::factory()->create([
            'category' => $category,
            'cts_positions' => [$callsign],
        ]);
    }

    private function createAccountWithMember(): Account
    {
        $account = Account::factory()->create();
        Member::factory()->create(['id' => $account->id, 'cid' => $account->id]);

        return $account->fresh();
    }

    private function createCompletedTrainingPlace(
        Account $account,
        TrainingPosition $trainingPosition,
        Carbon $createdAt,
        Carbon $deletedAt,
    ): TrainingPlace {
        $place = TrainingPlace::factory()->create([
            'account_id' => $account->id,
            'training_position_id' => $trainingPosition->id,
            'created_at' => $createdAt,
        ]);

        $place->delete();
        $place->forceFill(['deleted_at' => $deletedAt])->save();

        return $place->fresh(['account', 'trainingPosition']);
    }

    private function createCompletedSession(int $studentId, string $callsign, Carbon $takenDate): Session
    {
        return Session::factory()->create([
            'student_id' => $studentId,
            'position' => $callsign,
            'taken_date' => $takenDate->toDateString(),
            'taken_from' => '10:00:00',
            'taken_to' => '11:00:00',
            'cancelled_datetime' => null,
            'noShow' => 0,
            'session_done' => 1,
        ]);
    }
}
