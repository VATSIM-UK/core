<?php

namespace Tests\Feature\Admin\Pages\Operations;

use App\Filament\Pages\Operations\GenerateQuarterlyStats;
use Database\Factories\Cts\SessionFactory;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class GenerateQuarterlyStatsPageTest extends BaseAdminTestCase
{
    public function test_it_loads_if_authorised()
    {
        $this->actingAsAdminUser();
        $this->get(GenerateQuarterlyStats::getUrl())->assertSuccessful();
    }

    public function test_it_generates_stats()
    {
        $this->actingAsSuperUser();
        Livewire::test(GenerateQuarterlyStats::class)
            ->fillForm([
                'quarter' => '01-01',
                'year' => '2020',
            ])
            ->call('submit')
            ->assertOk();
    }

    public function test_it_returns_session_count()
    {
        // Arrange: create 10 P1_PPL(A) sessions in Q1 2020
        SessionFactory::new()
            ->withPilotTraining()
            ->withStartDate('2020-01-01')
            ->withEndDate('2020-03-31')
            ->withPosition('P1_PPL(A)')
            ->count(10)
            ->create();

        // Create 3 P1_PPL(A) sessions in April 2020 (should not be counted)
        SessionFactory::new()
            ->withPilotTraining()
            ->withStartDate('2020-04-01')
            ->withEndDate('2020-04-30')
            ->withPosition('P1_PPL(A)')
            ->count(3)
            ->create();

        // Create 2 P2_SEIR(A) sessions in Q1 2020 (should not be counted for P1)
        SessionFactory::new()
            ->withPilotTraining()
            ->withStartDate('2020-01-01')
            ->withEndDate('2020-03-31')
            ->withPosition('P2_SEIR(A)')
            ->count(2)
            ->create();

        $this->actingAsSuperUser();
        $component = Livewire::test(GenerateQuarterlyStats::class)
            ->fillForm([
                'quarter' => '01-01',
                'year' => '2020',
            ])
            ->call('submit');

        $statistics = $component->get('statistics');
        $p1Sessions = collect($statistics['P1'])->firstWhere('name', 'P1 Sessions');
        $this->assertEquals(10, $p1Sessions['value']);
    }

    public function test_it_returns_exam_count()
    {
        // Arrange: create 5 exams for P1_PPL(A), 3 passes, in Q1 2020
        $examTable = app('db')->connection('cts')->table('practical_results');
        $examTable->insert([
            ['date' => '2020-01-10', 'exam' => 'P1_PPL(A)', 'result' => 'P', 'student_id' => 1],
            ['date' => '2020-02-15', 'exam' => 'P1_PPL(A)', 'result' => 'F', 'student_id' => 2],
            ['date' => '2020-03-01', 'exam' => 'P1_PPL(A)', 'result' => 'P', 'student_id' => 3],
            ['date' => '2020-03-15', 'exam' => 'P1_PPL(A)', 'result' => 'P', 'student_id' => 4],
            ['date' => '2020-01-20', 'exam' => 'P1_PPL(A)', 'result' => 'F', 'student_id' => 5],
        ]);
        // Add an exam outside the range
        $examTable->insert([
            ['date' => '2020-04-01', 'exam' => 'P1_PPL(A)', 'result' => 'P', 'student_id' => 6],
        ]);
        // Act
        $this->actingAsSuperUser();
        $component = Livewire::test(GenerateQuarterlyStats::class)
            ->fillForm([
                'quarter' => '01-01',
                'year' => '2020',
            ])
            ->call('submit');
        // Assert
        $statistics = $component->get('statistics');
        $p1Exams = collect($statistics['P1'])->firstWhere('name', 'P1 Exams (total / passes)');
        $this->assertEquals('5 / 3', $p1Exams['value']);
    }

    public function test_it_returns_student_count()
    {
        // Arrange: create sessions and exams for unique students
        SessionFactory::new()
            ->withPilotTraining()
            ->withStartDate('2020-01-01')
            ->withEndDate('2020-03-31')
            ->withPosition('P1_PPL(A)')
            ->count(2)
            ->state(function ($attrs, $i) {
                return ['student_id' => $i + 10];
            })
            ->create();
        $examTable = app('db')->connection('cts')->table('practical_results');
        $examTable->insert([
            ['date' => '2020-02-01', 'exam' => 'P1_PPL(A)', 'result' => 'P', 'student_id' => 20],
            ['date' => '2020-03-01', 'exam' => 'P1_PPL(A)', 'result' => 'F', 'student_id' => 21],
        ]);
        // Act
        $this->actingAsSuperUser();
        $component = Livewire::test(GenerateQuarterlyStats::class)
            ->fillForm([
                'quarter' => '01-01',
                'year' => '2020',
            ])
            ->call('submit');
        // Assert
        $statistics = $component->get('statistics');
        $uniqueStudents = collect($statistics['General'])->firstWhere('name', 'Unique Students');
        $this->assertEquals(4, $uniqueStudents['value']);
    }

    public function test_it_returns_mentor_count()
    {
        // Arrange: create sessions with unique mentors
        SessionFactory::new()
            ->withPilotTraining()
            ->withStartDate('2020-01-01')
            ->withEndDate('2020-03-31')
            ->withPosition('P1_PPL(A)')
            ->count(2)
            ->state(function ($attrs, $i) {
                return ['mentor_id' => $i + 100];
            })
            ->create();
        // Act
        $this->actingAsSuperUser();
        $component = Livewire::test(GenerateQuarterlyStats::class)
            ->fillForm([
                'quarter' => '01-01',
                'year' => '2020',
            ])
            ->call('submit');
        // Assert
        $statistics = $component->get('statistics');
        $uniqueMentors = collect($statistics['General'])->firstWhere('name', 'Unique Mentors');
        $this->assertEquals(2, $uniqueMentors['value']);
    }

    public function test_it_returns_mentor_stats()
    {
        // Arrange: create mentors and members for mentorStats
        $db = app('db')->connection('cts');
        $db->table('members')->insert([
            ['id' => 200, 'cid' => 1234, 'name' => 'Mentor One'],
            ['id' => 201, 'cid' => 5678, 'name' => 'Mentor Two'],
        ]);
        SessionFactory::new()
            ->withPilotTraining()
            ->withStartDate('2020-01-01')
            ->withEndDate('2020-03-31')
            ->withPosition('P1_PPL(A)')
            ->count(3)
            ->state(function ($attrs, $i) {
                return ['mentor_id' => 200];
            })
            ->create();
        SessionFactory::new()
            ->withPilotTraining()
            ->withStartDate('2020-01-01')
            ->withEndDate('2020-03-31')
            ->withPosition('P1_PPL(A)')
            ->count(2)
            ->state(function ($attrs, $i) {
                return ['mentor_id' => 201];
            })
            ->create();
        // Act
        $this->actingAsSuperUser();
        $component = Livewire::test(GenerateQuarterlyStats::class)
            ->fillForm([
                'quarter' => '01-01',
                'year' => '2020',
            ])
            ->call('submit');
        // Assert
        $statistics = $component->get('statistics');
        $mentorStats = $statistics['P1 Mentor Session Count'];
        $this->assertEquals([
            ['name' => 'Mentor One (1234)', 'value' => 3],
            ['name' => 'Mentor Two (5678)', 'value' => 2],
        ], $mentorStats);
    }
}
