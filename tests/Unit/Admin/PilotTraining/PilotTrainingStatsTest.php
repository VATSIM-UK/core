<?php

namespace Tests\Unit\Admin\PilotTraining;

use App\Models\Cts\Member;
use App\Models\Cts\PracticalResult;
use App\Models\Cts\Session;
use App\Services\Admin\PilotTrainingStats;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PilotTrainingStatsTest extends TestCase
{
    #[Test]
    public function it_counts_sessions()
    {
        Session::factory()->create([
            'position' => 'P1_PPL(A)',
            'taken_date' => '2020-02-01',
        ]);

        // different position
        Session::factory()->create([
            'position' => 'P2_SEIR(A)',
            'taken_date' => '2020-02-01',
        ]);

        // different quarter
        Session::factory()->create([
            'position' => 'P1_PPL(A)',
            'taken_date' => '2020-04-01',
        ]);

        $count = PilotTrainingStats::sessionCount(
            Carbon::parse('2020-01-01'),
            Carbon::parse('2020-03-01'),
            'P1_PPL(A)'
        );
        $this->assertEquals(1, $count, 'Should count only sessions for P1_PPL(A) in Q1 2020');
    }

    #[Test]
    public function it_counts_exams()
    {
        PracticalResult::factory()->create([
            'exam' => 'P1',
            'result' => 'P',
            'date' => '2020-02-01',
        ]);
        PracticalResult::factory()->create([
            'exam' => 'P2',
            'result' => 'P',
            'date' => '2020-02-01',
        ]);

        // failed
        PracticalResult::factory()->create([
            'exam' => 'P1',
            'result' => 'F',
            'date' => '2020-02-01',
        ]);
        // different quarter
        PracticalResult::factory()->create([
            'exam' => 'P1',
            'result' => 'P',
            'date' => '2020-04-01',
        ]);

        $result = PilotTrainingStats::examCount(
            Carbon::parse('2020-01-01'),
            Carbon::parse('2020-03-01'),
            'P1'
        );
        $this->assertEquals('2 / 1', $result, 'Should count 2 P1 exams (1 pass) in Q1 2020');
    }

    #[Test]
    public function it_counts_unique_students()
    {
        Session::factory()->create([
            'student_id' => 1,
            'position' => 'P1_PPL(A)',
            'taken_date' => '2020-02-01',
        ]);
        Session::factory()->create([
            'student_id' => 2,
            'position' => 'P2_SEIR(A)',
            'taken_date' => '2020-02-01',
        ]);
        Session::factory()->create([
            'student_id' => 1,
            'position' => 'P1_PPL(A)',
            'taken_date' => '2020-04-01',
        ]);

        PracticalResult::factory()->create([
            'student_id' => 1,
            'exam' => 'P1',
            'result' => 'P',
            'date' => '2020-02-01',
        ]);
        PracticalResult::factory()->create([
            'student_id' => 2,
            'exam' => 'P2',
            'result' => 'P',
            'date' => '2020-02-01',
        ]);
        PracticalResult::factory()->create([
            'student_id' => 3,
            'exam' => 'P1',
            'result' => 'F',
            'date' => '2020-02-01',
        ]);
        PracticalResult::factory()->create([
            'student_id' => 1,
            'exam' => 'P1',
            'result' => 'P',
            'date' => '2020-04-01',
        ]);

        $count = PilotTrainingStats::studentCount(
            Carbon::parse('2020-01-01'),
            Carbon::parse('2020-03-01')
        );
        $this->assertEquals(3, $count, 'Should count 3 unique students in Q1 2020 (from sessions and exams)');
    }

    #[Test]
    public function it_counts_unique_mentors()
    {
        Session::factory()->create([
            'mentor_id' => 1,
            'position' => 'P1_PPL(A)',
            'taken_date' => '2020-02-01',
        ]);
        Session::factory()->create([
            'mentor_id' => 2,
            'position' => 'P2_SEIR(A)',
            'taken_date' => '2020-02-01',
        ]);
        Session::factory()->create([
            'mentor_id' => 1,
            'position' => 'P1_PPL(A)',
            'taken_date' => '2020-04-01',
        ]);

        $count = PilotTrainingStats::mentorCount(
            Carbon::parse('2020-01-01'),
            Carbon::parse('2020-03-01')
        );
        $this->assertEquals(2, $count, 'Should count 2 unique mentors in Q1 2020');
    }

    #[Test]
    public function it_counts_mentor_stats()
    {
        factory(Member::class)->create([
            'id' => 1,
            'cid' => '123456',
        ]);
        factory(Member::class)->create([
            'id' => 2,
            'cid' => '654321',
        ]);

        Session::factory()->create([
            'mentor_id' => 1,
            'position' => 'P1_PPL(A)',
            'taken_date' => '2020-02-01',
        ]);
        // different position
        Session::factory()->create([
            'mentor_id' => 2,
            'position' => 'P2_SEIR(A)',
            'taken_date' => '2020-02-01',
        ]);
        // different quarter
        Session::factory()->create([
            'mentor_id' => 1,
            'position' => 'P1_PPL(A)',
            'taken_date' => '2020-04-01',
        ]);

        $stats = PilotTrainingStats::mentorStats(
            Carbon::parse('2020-01-01'),
            Carbon::parse('2020-03-01'),
            'P1_PPL(A)'
        );

        $this->assertCount(1, $stats, 'Should only include mentor 1 for P1_PPL(A) in Q1 2020');
        $this->assertEquals(1, $stats[1]['session_count'], 'Mentor 1 should have 1 session in Q1 2020 for P1_PPL(A)');
    }

    #[Test]
    public function it_counts_student_stats()
    {
        // Create students
        factory(Member::class)->create([
            'id' => 1,
            'cid' => '111111',
            'name' => 'Student One',
        ]);
        factory(Member::class)->create([
            'id' => 2,
            'cid' => '222222',
            'name' => 'Student Two',
        ]);
        factory(Member::class)->create([
            'id' => 3,
            'cid' => '333333',
            'name' => 'Student Three',
        ]);

        // Sessions for students
        Session::factory()->create([
            'student_id' => 1,
            'position' => 'P1_PPL(A)',
            'taken_date' => '2020-02-01',
        ]);
        Session::factory()->create([
            'student_id' => 1,
            'position' => 'P1_PPL(A)',
            'taken_date' => '2020-02-10',
        ]);
        Session::factory()->create([
            'student_id' => 2,
            'position' => 'P1_PPL(A)',
            'taken_date' => '2020-02-05',
        ]);
        // different position
        Session::factory()->create([
            'student_id' => 3,
            'position' => 'P2_SEIR(A)',
            'taken_date' => '2020-02-01',
        ]);
        // different quarter
        Session::factory()->create([
            'student_id' => 1,
            'position' => 'P1_PPL(A)',
            'taken_date' => '2020-04-01',
        ]);

        $stats = PilotTrainingStats::studentStats(
            Carbon::parse('2020-01-01'),
            Carbon::parse('2020-03-01'),
            'P1_PPL(A)'
        );

        $this->assertCount(2, $stats, 'Should only include students 1 and 2 for P1_PPL(A) in Q1 2020');
        $this->assertEquals([
            'cid' => '111111',
            'name' => 'Student One',
            'session_count' => 2,
        ], $stats[1], 'Student 1 should have 2 sessions in Q1 2020 for P1_PPL(A)');
        $this->assertEquals([
            'cid' => '222222',
            'name' => 'Student Two',
            'session_count' => 1,
        ], $stats[2], 'Student 2 should have 1 session in Q1 2020 for P1_PPL(A)');
    }
}
