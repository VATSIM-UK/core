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
        Session::factory()->create([
            'date_1' => '2020-01-15',
            'from_1' => '10:00:00',
            'to_1' => '12:00:00',
            'position' => 'P1_PPL(A)',
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
}
