<?php

namespace Tests\Feature\Admin\Pages\Operations;

use App\Filament\Pages\Operations\GenerateQuarterlyStats;
use Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class GenerateQuarterlyStatsPageTest extends BaseAdminTestCase
{
    public function test_it_loads_if_authorised()
    {
        $this->actingAsAdminUser();
        $this->get(GenerateQuarterlyStats::getUrl())->assertForbidden();

        $this->adminUser->givePermissionTo('operations.access');
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
            ->assertSee('Results for Q1 2020');
    }
}
