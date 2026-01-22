<?php

namespace Tests\Feature\VisitTransfer\Application;

use App\Filament\Admin\Resources\VisitTransfer\VisitTransferApplicationResource\Pages\ListVisitTransferApplications;
use App\Models\VisitTransfer\Application;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminTestCase;

class ApplicationTableTest extends BaseAdminTestCase
{
    protected array $applications = [];

    protected function setUp(): void
    {
        parent::setUp();

        Application::factory()->count(3)->create();

    }

    #[Test]
    public function it_loads_if_user_has_basic_access()
    {
        $this->adminUser->givePermissionTo('vt.application.view.*');

        Livewire::actingAs($this->adminUser)
            ->test(ListVisitTransferApplications::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_does_not_load_if_user_lacks_basic_access()
    {
        Livewire::actingAs($this->adminUser)
            ->test(ListVisitTransferApplications::class)
            ->assertForbidden();
    }

    public function test_transfer_applications_are_listed_in_table()
    {
        $this->adminUser->givePermissionTo('vt.application.view.*');

        $component = Livewire::actingAs($this->adminUser)
            ->test(ListVisitTransferApplications::class, ['type' => Application::TYPE_TRANSFER])
            ->assertSuccessful();

        $applications = Application::where('type', Application::TYPE_TRANSFER)->get();

        foreach ($applications as $application) {
            $component->assertSee($application->public_id);
        }
    }

    public function test_visiting_applications_are_listed_in_table()
    {
        $this->adminUser->givePermissionTo('vt.application.view.*');

        $component = Livewire::actingAs($this->adminUser)
            ->test(ListVisitTransferApplications::class, ['type' => Application::TYPE_VISIT])
            ->assertSuccessful();

        $applications = Application::where('type', Application::TYPE_VISIT)->get();

        foreach ($applications as $application) {
            $component->assertSee($application->public_id);
        }
    }

    public function test_transfer_applications_are_hidden_when_visiting_type_selected()
    {
        $this->adminUser->givePermissionTo('vt.application.view.*');

        $component = Livewire::actingAs($this->adminUser)
            ->test(ListVisitTransferApplications::class, ['type' => Application::TYPE_VISIT])
            ->assertSuccessful();

        $applications = Application::where('type', Application::TYPE_TRANSFER)->get();

        foreach ($applications as $application) {
            $component->assertDontSee($application->public_id);
        }
    }
}
