<?php

namespace Tests\Feature\Admin\Pages\PilotTraining;

use App\Filament\Pages\PilotTraining\GeneratePilotQuarterlyStats;
use App\Models\Cts\PracticalResult;
use App\Models\Cts\Session;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class GenerateQuarterlyPilotStatsPageTest extends BaseAdminTestCase
{
    public function test_it_loads_if_authorised()
    {
        $this->actingAsAdminUser();
        $this->get(GeneratePilotQuarterlyStats::getUrl())->assertSuccessful();
    }

    public function test_it_generates_stats()
    {
        $this->actingAsSuperUser();
        Livewire::test(GeneratePilotQuarterlyStats::class)
            ->fillForm([
                'quarter' => '01-01',
                'year' => '2020',
            ])
            ->call('submit')
            ->assertOk();
    }

    public function test_it_returns_session_count()
    {
        Session::factory()->create([
            'position' => 'P1_PPL(A)',
            'taken_date' => '2020-02-01',
        ]);

        $this->actingAsSuperUser();
        $component = Livewire::test(GeneratePilotQuarterlyStats::class)
            ->fillForm([
                'quarter' => '01-01',
                'year' => '2020',
            ])
            ->call('submit');

        $statistics = $component->get('statistics');
        $p1Sessions = collect($statistics['P1'])->firstWhere('name', 'P1 Sessions');
        $this->assertEquals(1, $p1Sessions['value']);
    }

    public function test_it_returns_exam_count()
    {
        PracticalResult::factory()->create([
            'exam' => 'P1',
            'result' => 'P',
            'date' => '2020-02-01',
        ]);

        $this->actingAsSuperUser();
        $component = Livewire::test(GeneratePilotQuarterlyStats::class)
            ->fillForm([
                'quarter' => '01-01',
                'year' => '2020',
            ])
            ->call('submit');

        $statistics = $component->get('statistics');
        $p1Exams = collect($statistics['P1'])->firstWhere('name', 'P1 Exams (total / passes)');
        $this->assertEquals('1 / 1', $p1Exams['value']);
    }

    public function test_it_generates_csv()
    {
        $this->actingAsSuperUser();
        Livewire::test(GeneratePilotQuarterlyStats::class)
            ->fillForm([
                'quarter' => '01-01',
                'year' => '2020',
            ])
            ->call('exportSessionsCsv')
            ->assertOk();
    }
}
