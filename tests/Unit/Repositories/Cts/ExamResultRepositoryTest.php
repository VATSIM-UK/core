<?php

namespace Tests\Unit\Repositories\Cts;

use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalResult;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Repositories\Cts\ExamResultRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExamResultRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    protected ExamResultRepository $repository;

    protected Account $user;

    protected array $examBookings = [];

    protected array $practicalResults = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(ExamResultRepository::class);
        $this->user = Account::factory()->create(['id' => 9000000]);
        Member::factory()->create(['id' => $this->user->id, 'cid' => $this->user->id]);

        // Create exam data for all levels
        $this->createExamData();
    }

    private function createExamData(): void
    {
        $examLevels = ['OBS', 'TWR', 'APP', 'CTR'];

        foreach ($examLevels as $level) {
            // Create a student account and member
            $student = Account::factory()->create();
            $studentMember = Member::factory()->create([
                'id' => $student->id,
                'cid' => $student->id,
            ]);

            // Create an exam booking
            $examBooking = ExamBooking::factory()->create([
                'taken' => 1,
                'finished' => ExamBooking::FINISHED_FLAG,
                'exam' => $level,
                'student_id' => $studentMember->id,
                'student_rating' => Qualification::code('S1')->first()->vatsim,
                'position_1' => 'EGKK_'.$level,
            ]);

            // Create examiners
            $examBooking->examiners()->create([
                'examid' => $examBooking->id,
                'senior' => $this->user->id,
            ]);

            // Create a practical result
            $practicalResult = PracticalResult::factory()->create([
                'examid' => $examBooking->id,
                'student_id' => $studentMember->id,
                'result' => PracticalResult::PASSED,
                'exam' => $level,
                'date' => now()->subDays(rand(1, 30)),
            ]);

            $this->examBookings[$level] = $examBooking;
            $this->practicalResults[$level] = $practicalResult;
        }
    }

    #[Test]
    public function it_filters_exam_history_by_exam_levels_collection()
    {
        $examLevels = collect(['OBS', 'TWR']);

        $query = $this->repository->getExamHistoryQueryForLevels($examLevels);
        $results = $query->get();

        // Should only return OBS and TWR results from our test data
        $obsResults = $results->where('exam', 'OBS');
        $twrResults = $results->where('exam', 'TWR');
        $appResults = $results->where('exam', 'APP');
        $ctrResults = $results->where('exam', 'CTR');

        $this->assertGreaterThanOrEqual(1, $obsResults->count(), 'Should contain at least 1 OBS result');
        $this->assertGreaterThanOrEqual(1, $twrResults->count(), 'Should contain at least 1 TWR result');
        $this->assertEquals(0, $appResults->count(), 'Should contain no APP results');
        $this->assertEquals(0, $ctrResults->count(), 'Should contain no CTR results');
    }

    #[Test]
    public function it_returns_empty_query_when_no_exam_levels()
    {
        $examLevels = collect([]); // No exam levels

        $query = $this->repository->getExamHistoryQueryForLevels($examLevels);
        $results = $query->get();

        // Should return no results
        $this->assertCount(0, $results);
    }

    #[Test]
    public function it_returns_all_exams_when_all_levels_provided()
    {
        $examLevels = collect(['OBS', 'TWR', 'APP', 'CTR']);

        $query = $this->repository->getExamHistoryQueryForLevels($examLevels);
        $results = $query->get();

        // Should return all 4 exam types from our test data
        $this->assertTrue($results->contains('exam', 'OBS'), 'Should contain OBS results');
        $this->assertTrue($results->contains('exam', 'TWR'), 'Should contain TWR results');
        $this->assertTrue($results->contains('exam', 'APP'), 'Should contain APP results');
        $this->assertTrue($results->contains('exam', 'CTR'), 'Should contain CTR results');

        // Verify we have at least our 4 test results
        $this->assertGreaterThanOrEqual(4, $results->count(), 'Should contain at least 4 results');
    }

    #[Test]
    public function it_accepts_collection_of_exam_levels()
    {
        $examLevels = collect(['OBS', 'TWR']);

        $query = $this->repository->getExamHistoryQueryForLevels($examLevels);
        $results = $query->get();

        // Should only return OBS and TWR results from our test data
        $obsResults = $results->where('exam', 'OBS');
        $twrResults = $results->where('exam', 'TWR');
        $appResults = $results->where('exam', 'APP');
        $ctrResults = $results->where('exam', 'CTR');

        $this->assertGreaterThanOrEqual(1, $obsResults->count(), 'Should contain at least 1 OBS result');
        $this->assertGreaterThanOrEqual(1, $twrResults->count(), 'Should contain at least 1 TWR result');
        $this->assertEquals(0, $appResults->count(), 'Should contain no APP results');
        $this->assertEquals(0, $ctrResults->count(), 'Should contain no CTR results');
    }

    #[Test]
    public function it_filters_single_exam_level()
    {
        $examLevels = collect(['CTR']);

        $query = $this->repository->getExamHistoryQueryForLevels($examLevels);
        $results = $query->get();

        // Should return only CTR results from our test data
        $obsResults = $results->where('exam', 'OBS');
        $twrResults = $results->where('exam', 'TWR');
        $appResults = $results->where('exam', 'APP');
        $ctrResults = $results->where('exam', 'CTR');

        $this->assertEquals(0, $obsResults->count(), 'Should contain no OBS results');
        $this->assertEquals(0, $twrResults->count(), 'Should contain no TWR results');
        $this->assertEquals(0, $appResults->count(), 'Should contain no APP results');
        $this->assertGreaterThanOrEqual(1, $ctrResults->count(), 'Should contain at least 1 CTR result');
    }

    #[Test]
    public function it_includes_proper_relationships_in_query()
    {
        $examLevels = collect(['TWR']);

        $query = $this->repository->getExamHistoryQueryForLevels($examLevels);

        // Verify that the query includes the expected relationships
        $this->assertArrayHasKey('student', $query->getEagerLoads());
        $this->assertArrayHasKey('examBooking', $query->getEagerLoads());
    }

    #[Test]
    public function it_returns_builder_instance()
    {
        $examLevels = collect(['OBS']);

        $query = $this->repository->getExamHistoryQueryForLevels($examLevels);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $query);
    }

    #[Test]
    public function it_handles_empty_exam_levels_collection()
    {
        $examLevels = collect([]);

        $query = $this->repository->getExamHistoryQueryForLevels($examLevels);
        $results = $query->get();

        // Should return no results
        $this->assertCount(0, $results);
    }

    #[Test]
    public function it_handles_invalid_exam_levels()
    {
        $examLevels = collect(['INVALID', 'NONEXISTENT']);

        $query = $this->repository->getExamHistoryQueryForLevels($examLevels);
        $results = $query->get();

        // Should return no results since none of the levels match existing exam types
        $this->assertCount(0, $results);
    }
}
