<?php

namespace Tests\Unit\Training;

use App\Jobs\Training\SyncTheoryExamFromMoodle;
use App\Jobs\Training\SyncTheoryResultsFromMoodle;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SyncTheoryResultsFromMoodleTest extends TestCase
{
    public function test_dispatches_one_sync_per_configured_exam_and_skips_null_levels(): void
    {
        Queue::fake();

        config(['services.moodle.theory_exams' => [
            'S1' => 101,
            'S2' => null,
            'S3' => 303,
            'C1' => null,
        ]]);

        (new SyncTheoryResultsFromMoodle)->handle();

        Queue::assertPushed(SyncTheoryExamFromMoodle::class, 2);
        Queue::assertPushed(SyncTheoryExamFromMoodle::class, fn (SyncTheoryExamFromMoodle $job) => $this->jobHas($job, 'S1', 101));
        Queue::assertPushed(SyncTheoryExamFromMoodle::class, fn (SyncTheoryExamFromMoodle $job) => $this->jobHas($job, 'S3', 303));
    }

    public function test_dispatches_nothing_when_every_level_is_null(): void
    {
        Queue::fake();

        config(['services.moodle.theory_exams' => ['S1' => null, 'S2' => null, 'S3' => null, 'C1' => null]]);

        (new SyncTheoryResultsFromMoodle)->handle();

        Queue::assertNothingPushed();
    }

    private function jobHas(SyncTheoryExamFromMoodle $job, string $exam, int $quizId): bool
    {
        $reflection = new \ReflectionClass($job);

        return $reflection->getProperty('exam')->getValue($job) === $exam
            && $reflection->getProperty('quizId')->getValue($job) === $quizId;
    }
}
