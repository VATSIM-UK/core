<?php

namespace Tests\Feature\Admin\Pages\ATCTraining;

use App\Filament\Admin\Pages\ATCTraining\GenerateATCTrainingQuarterlyStats;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class GenerateQuarterlyATCStatsPageTest extends BaseAdminTestCase
{
    public function test_it_loads_if_authorised()
    {
        $this->actingAsAdminUser();
        $this->get(GenerateATCTrainingQuarterlyStats::getUrl())->assertForbidden();

        $this->adminUser->givePermissionTo('atc.stats');
        $this->get(GenerateATCTrainingQuarterlyStats::getUrl())->assertSuccessful();
    }

    public function test_it_generates_stats()
    {
        $this->actingAsSuperUser();
        Livewire::test(GenerateATCTrainingQuarterlyStats::class)
            ->fillForm([
                'quarter' => '01-01',
                'year' => '2020',
            ])
            ->call('submit')
            ->assertOk();
    }
}
