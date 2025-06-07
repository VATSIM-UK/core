<?php

namespace Tests\Feature\Admin\Pages\PilotTraining;

use App\Filament\Pages\PilotTraining\GeneratePilotQuarterlyStats;
use App\Models\Cts\Member;
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
        factory(Session::class)->new()
            ->withPilotTraining()
            ->withStartDate('2020-01-01')
            ->withEndDate('2020-03-31')
            ->withPosition('P1_PPL(A)')
            ->count(10)
            ->create();

        $this->actingAsSuperUser();
        $component = Livewire::test(GeneratePilotQuarterlyStats::class)
            ->fillForm([
                'quarter' => '01-01',
                'year' => '2020',
            ])
            ->call('submit');

        $statistics = $component->get('statistics');
        $p1Sessions = collect($statistics['P1'])->firstWhere('name', 'P1 Sessions');
        $this->assertEquals(10, $p1Sessions['value']);
    }

    public function test_statistics_structure_and_keys()
    {
        $this->actingAsSuperUser();
        $component = Livewire::test(GeneratePilotQuarterlyStats::class)
            ->fillForm([
                'quarter' => '01-01',
                'year' => '2020',
            ])
            ->call('submit');

        $statistics = $component->get('statistics');
        $this->assertArrayHasKey('P1', $statistics);
        $this->assertArrayHasKey('P2', $statistics);
        $this->assertArrayHasKey('TFP', $statistics);
        $this->assertArrayHasKey('General', $statistics);
        $this->assertArrayHasKey('P1 Mentor Session Count', $statistics);
        $this->assertArrayHasKey('P2 Mentor Session Count', $statistics);
        $this->assertArrayHasKey('TFP Mentor Session Count', $statistics);
    }

    public function test_mentor_session_counts_are_formatted()
    {
        // Create sessions for a mentor
        $mentor = factory(Member::class)->create();
        factory(Session::class)->new()
            ->withPilotTraining()
            ->withStartDate('2020-01-01')
            ->withEndDate('2020-03-31')
            ->withPosition('P1_PPL(A)')
            ->withMentor($mentor)
            ->count(3)
            ->create();

        $this->actingAsSuperUser();
        $component = Livewire::test(GeneratePilotQuarterlyStats::class)
            ->fillForm([
                'quarter' => '01-01',
                'year' => '2020',
            ])
            ->call('submit');

        $statistics = $component->get('statistics');
        $p1Mentors = $statistics['P1 Mentor Session Count'];
        $this->assertNotEmpty($p1Mentors);
        $this->assertStringContainsString((string) $mentor->cid, $p1Mentors[0]['name']);
        $this->assertEquals(3, $p1Mentors[0]['value']);
    }

    public function test_export_sessions_csv_dispatches_event()
    {
        $this->actingAsSuperUser();
        $component = Livewire::test(GeneratePilotQuarterlyStats::class)
            ->fillForm([
                'quarter' => '01-01',
                'year' => '2020',
            ]);
        $component->call('exportSessionsCsv');
        $component->assertDispatched('download-csv');
    }

    public function test_validation_fails_if_fields_missing()
    {
        $this->actingAsSuperUser();
        Livewire::test(GeneratePilotQuarterlyStats::class)
            ->fillForm([
                'quarter' => null,
                'year' => null,
            ])
            ->call('submit')
            ->assertHasFormErrors(['quarter', 'year']);
    }
}
